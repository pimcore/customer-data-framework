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
use Pimcore\Model\DataObject\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SegmentAssignmentController
 *
 * @Route("/segment-assignment")
 *
 * @package CustomerManagementFrameworkBundle\Controller\Admin
 */
class SegmentAssignmentController extends UserAwareController
{
    use JsonHelperTrait;

    public function __construct(protected SegmentAssignerInterface $segmentAssigner)
    {
    }

    /**
     * @Route("/inheritable-segments")
     *
     * @throws Exception
     */
    public function inheritableSegments(Request $request, SegmentManagerInterface $segmentManager): JsonResponse
    {
        $id = $request->get('id');
        $type = $request->get('type');
        if (!$type || !$id) {
            return $this->jsonResponse(['data' => []]);
        }

        $db = \Pimcore\Db::get();
        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $parentIdField = Service::getVersionDependentDatabaseColumnName('parentId');

        $parentIdStatement = sprintf('SELECT :parentIdField FROM %s WHERE :idField = :value', $db->quoteIdentifier($type . 's'));
        $parentId = $db->fetchOne($parentIdStatement, [
            'parentIdField' => $parentIdField,
            'idField' => $idField,
            'value' => $id
        ]);

        $segments = $segmentManager->getSegmentsForElementId($parentId, $type);
        $data = array_map([$this, 'dehydrateSegment'], array_filter($segments));

        return $this->jsonResponse(['data' => array_values($data)]);
    }

    /**
     * returns directly assigned segmentIds for the pimcore backend
     *
     * @Route("/assigned-segments")
     *
     * @throws Exception
     */
    public function assignedSegments(Request $request): JsonResponse
    {
        $id = $request->get('id') ?? '';
        $type = $request->get('type') ?? '';
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
     *
     * @Route("/assign")
     */
    public function assign(Request $request): JsonResponse
    {
        $id = $request->get('id') ?? '';
        $type = $request->get('type') ?? '';
        $breaksInheritance = $request->get('breaksInheritance') === 'true';
        $segmentIds = json_decode($request->get('segmentIds'), true) ?? [];

        $success = $this->segmentAssigner->assignById($id, $type, $breaksInheritance, $segmentIds);

        return $this->jsonResponse($success);
    }

    /**
     * @Route("/breaks-inheritance")
     */
    public function breaksInheritance(Request $request): JsonResponse
    {
        $id = $request->get('id') ?? '';
        $type = $request->get('type') ?? '';
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
