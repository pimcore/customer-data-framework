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

use CustomerManagementFrameworkBundle\GDPR\DataProvider\Customers;
use Pimcore\Controller\KernelControllerEventInterface;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DataObjectController
 *
 * @Route("/gdpr-data")
 */
class GDPRDataController extends UserAwareController implements KernelControllerEventInterface
{
    use JsonHelperTrait;

    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        $this->checkPermission('gdpr_data_extractor');
    }

    /**
     * @Route("/search-data-objects", name="_pimcore_customermanagementframework_gdprdata_searchdataobjects", methods={"GET"})
     */
    public function searchDataObjectsAction(Request $request, Customers $service): JsonResponse
    {
        $allParams = array_merge($request->request->all(), $request->query->all());

        $result = $service->searchData(
            intval($allParams['id']),
            strip_tags($allParams['firstname']),
            strip_tags($allParams['lastname']),
            strip_tags($allParams['email']),
            intval($allParams['start']),
            intval($allParams['limit']),
            $allParams['sort'] ?? null
        );

        return $this->jsonResponse($result);
    }

    /**
     * @Route("/export", name="_pimcore_customermanagementframework_gdprdata_export", methods={"GET"})
     */
    public function exportDataObjectAction(Request $request, Customers $service): JsonResponse
    {
        $object = AbstractObject::getById($request->query->getInt('id'));
        $exportResult = $service->doExportData($object);
        $jsonResponse = $this->jsonResponse($exportResult);
        $jsonResponse->headers->set('Content-Disposition', 'attachment; filename="export-data-object-' . $object->getId() . '.json"');

        return $jsonResponse;
    }
}
