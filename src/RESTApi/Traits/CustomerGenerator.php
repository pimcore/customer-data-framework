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

namespace CustomerManagementFrameworkBundle\RESTApi\Traits;

use CustomerManagementFrameworkBundle\Filter\ExportCustomersFilterParams;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait CustomerGenerator
{

    /**
     * Create customer response with hydrated customer data
     *
     * @param CustomerInterface $customer
     * @param Request $request
     * @param ExportCustomersFilterParams $params
     *
     * @return Response
     */
    protected function createCustomerResponse(
        CustomerInterface $customer,
        Request $request,
        ExportCustomersFilterParams $params = null
    ) {
        if (null === $params) {
            $params = ExportCustomersFilterParams::fromRequest($request);
        }

        $response = $this->createResponse(
            $this->hydrateCustomer($customer, $params)
        );

        return $response;
    }

    /**
     * @param CustomerInterface $customer
     * @param ExportCustomersFilterParams $params
     *
     * @return array
     */
    protected function hydrateCustomer(CustomerInterface $customer, ExportCustomersFilterParams $params)
    {
        $data = $customer->cmfToArray();

        if ($params->getIncludeActivities()) {
            $data['activities'] = \Pimcore::getContainer()->get('cmf.activity_store')->getActivityDataForCustomer(
                $customer
            );
        }

        $links = isset($data['_links']) ? $data['_links'] : [];

        if ($selfLink = $this->generateResourceApiUrl($customer->getId())) {
            $links[] = [
                'rel' => 'self',
                'href' => $selfLink,
                'method' => 'GET',
            ];
        }

        $data['_links'] = $links;

        return $data;
    }

}
