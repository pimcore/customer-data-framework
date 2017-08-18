<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\RestHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/segments-of-customers")
 */
class SegmentsOfCustomersController extends RestHandlerController
{
    /**
     * @param Request $request
     * @Route("")
     * @Method({"PUT", "POST"})
     */
    public function updateRecordsAction(Request $request)
    {
        $handler = $this->getHandler();
        $response = null;

        try {
            $response = $handler->updateRecords($request);
        } catch (ExceptionInterface $e) {
            $response = $this->createErrorResponse(
                $e->getMessage(),
                $e->getResponseCode() > 0 ? $e->getResponseCode() : 400
            );
        }

        return $response;
    }

    /**
     * @return \CustomerManagementFrameworkBundle\RESTApi\SegmentsOfCustomerHandler
     */
    protected function getHandler()
    {
        return \Pimcore::getContainer()->get('cmf.rest.segments_of_customer_handler');
    }
}
