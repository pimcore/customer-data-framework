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

namespace CustomerManagementFrameworkBundle\RESTApi;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class SegmentsOfCustomerHandler extends AbstractHandler
{
    use LoggerAware;

    public function __construct(
        PaginatorInterface $paginator,
        protected CustomerProviderInterface $customerProvider,
        protected SegmentManagerInterface $segmentManager
    ) {
        parent::__construct($paginator);
    }

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

        if (!$customer = $this->customerProvider->getById($data['customerId'])) {
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
                if ($segment = $this->segmentManager->getSegmentById($segmentId)) {
                    $addSegments[] = $segment;
                }
            }
        }

        $deleteSegments = [];
        if (is_array($data['removeSegments'])) {
            foreach ($data['removeSegments'] as $segmentId) {
                if ($segment = $this->segmentManager->getSegmentById($segmentId)) {
                    $deleteSegments[] = $segment;
                }
            }
        }

        $this->segmentManager->mergeSegments(
            $customer,
            $addSegments,
            $deleteSegments,
            'REST update API: segments-of-customer action'
        );
        $this->segmentManager->saveMergedSegments($customer);

        return new Response(['success' => true], Response::RESPONSE_CODE_OK);
    }
}
