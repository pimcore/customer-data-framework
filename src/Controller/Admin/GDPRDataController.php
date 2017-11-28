<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\GDPR\DataProvider\Customers;
use GDPRDataExtractorBundle\DataProvider\DataObjects;
use Pimcore\Model\DataObject\AbstractObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DataObjectController
 *
 * @Route("/gdpr-data")
 */
class GDPRDataController extends \Pimcore\Bundle\AdminBundle\Controller\AdminController
{

    /**
     * @param Request $request
     * @Route("/search-data-objects")
     */
    public function searchDataObjectsAction(Request $request, Customers $service) {

        $allParams = array_merge($request->request->all(), $request->query->all());

        $result = $service->searchData(
            intval($allParams['id']),
            strip_tags($allParams['firstname']),
            strip_tags($allParams['lastname']),
            strip_tags($allParams['email']),
            intval($allParams['start']),
            intval($allParams['limit']),
            $allParams['sort']
        );

        return $this->json($result);

    }

    /**
     * @param Request $request
     * @Route("/export")
     */
    public function exportDataObjectAction(Request $request, Customers $service) {

        $object = AbstractObject::getById($request->get("id"));
        $exportResult = $service->doExportData($object);
        $jsonResponse = $this->json($exportResult);
        $jsonResponse->headers->set('Content-Disposition', 'attachment; filename="export-data-object-' . $object->getId() . '.json"');

        return $jsonResponse;
    }

}
