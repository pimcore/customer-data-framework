<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\SegmentAssignment\SegmentAssigner\SegmentAssignerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Doctrine\DBAL\Exception;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject\CustomerSegment;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class SegmentAssignmentController
 *
 * @package CustomerManagementFrameworkBundle\Controller\Admin
 */
#[Route('/segment-assignment')]
class SegmentAssignmentController extends UserAwareController
{
    use JsonHelperTrait;

    public function __construct(protected SegmentAssignerInterface $segmentAssigner)
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/inheritable-segments')]
    public function inheritableSegments(Request $request, SegmentManagerInterface $segmentManager): JsonResponse
    {
        $id = $request->query->getInt('id');
        $type = $request->query->getString('type');
        if (!$type || !$id) {
            return $this->jsonResponse(['data' => []]);
        }

        $db = \Pimcore\Db::get();

        $parentIdStatement = sprintf('SELECT :parentIdField FROM %s WHERE :idField = :value', $db->quoteIdentifier($type . 's'));
        $parentId = $db->fetchOne($parentIdStatement, [
            'parentIdField' => 'parentId',
            'idField' => 'id',
            'value' => $id,
        ]);

        $segments = $segmentManager->getSegmentsForElementId($parentId, $type);
        $data = array_map([$this, 'dehydrateSegment'], array_filter($segments));

        return $this->jsonResponse(['data' => array_values($data)]);
    }

    /**
     * returns directly assigned segmentIds for the pimcore backend
     *
     * @throws Exception
     */
    #[Route('/assigned-segments')]
    public function assignedSegments(Request $request): JsonResponse
    {
        $id = $request->query->getInt('id');
        $type = $request->query->getString('type');
        $assignmentTable = $this->getParameter('cmf.segmentAssignment.table.raw');
        $segmentIds = \Pimcore\Db::get()->fetchOne("SELECT `segments` FROM $assignmentTable WHERE `elementId` = ? AND `elementType` = ?", [$id, $type]);

        $data = array_map(function (string $id) {
            $segment = CustomerSegment::getById((int) $id);

            return $this->dehydrateSegment($segment);
        }, array_filter(explode(',', $segmentIds)));

        return $this->jsonResponse(['data' => array_values($data)]);
    }

    /**
     * saves assignments asynchronously
     */
    #[Route('/assign')]
    public function assign(Request $request): JsonResponse
    {
        $id = $request->request->getString('id');
        $type = $request->request->getString('type');
        $breaksInheritance = $request->request->getBoolean('breaksInheritance');
        $segmentIds = json_decode($request->request->getString('segmentIds'), true) ?? [];

        $success = $this->segmentAssigner->assignById($id, $type, $breaksInheritance, $segmentIds);

        return $this->jsonResponse($success);
    }

    #[Route('/breaks-inheritance')]
    public function breaksInheritance(Request $request): JsonResponse
    {
        $id = $request->request->getString('id');
        $type = $request->request->getString('type');
        $assignmentTable = $this->getParameter('cmf.segmentAssignment.table.raw');

        $breaksInheritance = \Pimcore\Db::get()->fetchOne("SELECT `breaksInheritance` FROM $assignmentTable WHERE `elementId` = ? AND `elementType` = ?", [$id, $type]);

        return $this->jsonResponse(['breaksInheritance' => $breaksInheritance]);
    }

    /**
     * dehydrates a CustomerSegment for display in the pimcore backend
     *
     */
    private function dehydrateSegment(CustomerSegment $segment): array
    {
        return [
            'id' => $segment->getId(),
            'type' => $segment->getType(),
            'name' => $segment->getName(),
        ];
    }
}
