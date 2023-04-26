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

namespace CustomerManagementFrameworkBundle\Controller\Rest\Api;

use CustomerManagementFrameworkBundle\Controller\Rest\RestHandlerController;
use CustomerManagementFrameworkBundle\RESTApi\Exception\ExceptionInterface;
use CustomerManagementFrameworkBundle\RESTApi\Response;
use CustomerManagementFrameworkBundle\RESTApi\SegmentsOfCustomerHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/segments-of-customers")
 */
class SegmentsOfCustomersController extends RestHandlerController
{
    /**
     * @Route("", methods={"PUT", "POST"})
     */
    public function updateRecordsAction(Request $request): JsonResponse | Response
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
     * @return SegmentsOfCustomerHandler
     */
    protected function getHandler(): SegmentsOfCustomerHandler
    {
        return \Pimcore::getContainer()->get('cmf.rest.segments_of_customer_handler');
    }
}
