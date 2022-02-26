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

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\CustomerList\Filter\CustomerSegment as CustomerSegmentFilter;
use CustomerManagementFrameworkBundle\CustomerList\Filter\SearchQuery;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Listing\Filter\BoolCombinator;
use CustomerManagementFrameworkBundle\Listing\Filter\Equals;
use CustomerManagementFrameworkBundle\Listing\Filter\Permission as PermissionFilter;
use CustomerManagementFrameworkBundle\Listing\FilterHandler;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Pimcore\Model\DataObject\Listing\Concrete;
use Pimcore\Model\User;

class SearchHelper
{
    private $segmentManager;
    private $customerProvider;

    /**
     * SearchHelper constructor.
     *
     * @param SegmentManagerInterface $segmentManager
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(SegmentManagerInterface $segmentManager, CustomerProviderInterface $customerProvider)
    {
        $this->segmentManager = $segmentManager;
        $this->customerProvider = $customerProvider;
    }

    /**
     * Fetch segment manager
     *
     * @return SegmentManagerInterface
     */
    public function getSegmentManager(): SegmentManagerInterface
    {
        return $this->segmentManager;
    }

    /**
     * Fetch the CustomerProvider
     *
     * @return CustomerProviderInterface
     */
    public function getCustomerProvider(): CustomerProviderInterface
    {
        return $this->customerProvider;
    }

    /**
     * @return array
     */
    public function getConfiguredSearchBarFields()
    {
        $filterProperties = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.customer_list.filter_properties');
        $searchProperties = $filterProperties['search'];

        $searchBarFields = [];
        if (isset($searchProperties['search'])) {
            $searchProperties = $searchProperties['search'];

            if (is_array($searchProperties) && count($searchProperties) > 0) {
                $searchBarFields = array_values($searchProperties);
            }
        }

        return $searchBarFields;
    }

    /**
     * @param Concrete $listing
     * @param array $filters
     * @param User $adminUser
     *
     * @throws \Exception
     */
    public function addListingFilters(Concrete $listing, array $filters, User $adminUser)
    {
        $handler = new FilterHandler($listing);

        $operatorCustomer = 'AND';
        $operatorSegments = null;
        if (array_key_exists('operator-customer', $filters)) {
            $operatorCustomer = $filters['operator-customer'];
        }
        if (array_key_exists('operator-segments', $filters)) {
            $operatorSegments = $filters['operator-segments'];
        }

        $filterProperties = \Pimcore::getContainer()->getParameter('pimcore_customer_management_framework.customer_list.filter_properties');

        $equalsProperties = isset($filterProperties['equals']) ? $filterProperties['equals'] : [];
        $searchProperties = isset($filterProperties['search']) ? $filterProperties['search'] : [];

        foreach ($equalsProperties as $property => $databaseField) {
            if (array_key_exists($property, $filters)) {
                $handler->addFilter(new Equals($databaseField, $filters[$property]));
            }
        }

        $searchFilters = [];
        foreach ($searchProperties as $property => $databaseFields) {
            if (array_key_exists($property, $filters)
                && !empty($filters[$property])
                && is_string($filters[$property])) {
                $searchFilters[] = new SearchQuery($databaseFields, $filters[$property]);
            }
        }
        if (!empty($searchFilters)) {
            $handler->addFilter(new BoolCombinator($searchFilters, $operatorCustomer));
        }

        if (array_key_exists('segments', $filters)) {
            if ($operatorSegments == 'ANY') {
                $segments = [];
                foreach ($filters['segments'] as $groupId => $segmentIds) {
                    foreach ($segmentIds as $segmentId) {
                        $segment = $this->getSegmentManager()->getSegmentById($segmentId);
                        if (!$segment) {
                            throw new \Exception(sprintf('Segment %d was not found', $segmentId));
                        }
                        $segments[] = $segment;
                    }
                }
                $handler->addFilter(new CustomerSegmentFilter($segments, null, 'OR'));
            } else {
                foreach ($filters['segments'] as $groupId => $segmentIds) {
                    $segmentGroup = null;
                    if ($groupId !== 'default') {
                        $segmentGroup = $this->getSegmentManager()->getSegmentGroupById($groupId);
                        if (!$segmentGroup) {
                            throw new \Exception(sprintf('Segment group %d was not found', $groupId));
                        }
                    }
                    $segments = [];
                    foreach ($segmentIds as $segmentId) {
                        $segment = $this->getSegmentManager()->getSegmentById($segmentId);
                        if (!$segment) {
                            throw new \Exception(sprintf('Segment %d was not found', $segmentId));
                        }
                        $segments[] = $segment;
                    }
                    $handler->addFilter(new CustomerSegmentFilter($segments, $segmentGroup, $operatorSegments));
                }
            }
        }

        // add permission filter for non admin
        if (!$adminUser->isAdmin()) {
            // only show customers which the user can access
            $handler->addFilter(new PermissionFilter($adminUser));
        }
    }
}
