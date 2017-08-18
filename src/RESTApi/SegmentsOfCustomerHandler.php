<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class SegmentsOfCustomerHandler extends AbstractHandler
{
    use LoggerAware;

    protected function getRoutes()
    {
        $routes = new RouteCollection();

        $routes->add(
            'update',
            $this->createRoute('POST', '/', 'updateRecords')
        );

        return $routes;
    }

    /**
     * POST /segments-of-customer
     *
     * @param Request $request
     */
    public function updateRecords(Request $request)
    {
        $data = $this->getRequestData($request);

        if (empty($data['customerId'])) {
            return new Response(
                [
                    'success' => false,
                    'msg' => 'customerId required',
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        if (!$customer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($data['customerId'])) {
            return new Response(
                [
                    'success' => false,
                    'msg' => sprintf('customer with id %s not found', $data['customerId']),
                ],
                Response::RESPONSE_CODE_BAD_REQUEST
            );
        }

        $addSegments = [];
        if (is_array($data['addSegments'])) {
            foreach ($data['addSegments'] as $segmentId) {
                if ($segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId)) {
                    $addSegments[] = $segment;
                }
            }
        }

        $deleteSegments = [];
        if (is_array($data['removeSegments'])) {
            foreach ($data['removeSegments'] as $segmentId) {
                if ($segment = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentById($segmentId)) {
                    $deleteSegments[] = $segment;
                }
            }
        }

        \Pimcore::getContainer()->get('cmf.segment_manager')->mergeSegments(
            $customer,
            $addSegments,
            $deleteSegments,
            'REST update API: segments-of-customer action'
        );
        \Pimcore::getContainer()->get('cmf.segment_manager')->saveMergedSegments($customer);

        return new Response(['success' => true], Response::RESPONSE_CODE_OK);
    }
}
