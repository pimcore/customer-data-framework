<?php

use CustomerManagementFramework\Controller\Admin;
use CustomerManagementFramework\Controller\Traits\PaginatorController;
use CustomerManagementFramework\Listing\Filter;
use CustomerManagementFramework\Listing\Listing;
use Pimcore\Model\Object\Customer;
use Pimcore\Model\Object\CustomerSegment;

class CustomerManagementFramework_CustomersController extends Admin
{
    use PaginatorController;

    public function listAction()
    {
        $this->enableLayout();

        $this->loadSegmentGroup('gender');

        $filters   = $this->fetchListFilters();
        $listing   = $this->buildListing($filters);
        $paginator = $this->buildPaginator($listing->getListing());

        $this->view->paginator = $paginator;
    }

    protected function loadSegmentGroup($groupName)
    {
        /** @var \Pimcore\Model\Object\CustomerSegmentGroup $group */
        $group = \Pimcore\Model\Object\CustomerSegmentGroup::getByName($groupName, 1);
        if (!$group) {
            throw new InvalidArgumentException(sprintf('Segment group %s was not found', $groupName));
        }

        $segments = new CustomerSegment\Listing();
        $segments->addConditionParam('group__id IS NOT NULL AND group__id = ?', $group->getId());

        if (!isset($this->view->segments)) {
            $this->view->segments = [];
        }

        $this->view->segments[$group->getName()] = [];
        foreach ($segments as $segment) {
            $this->view->segments[$group->getName()][] = $segment;
        }
    }

    /**
     * @param array $filters
     * @return Listing
     */
    protected function buildListing(array $filters = [])
    {
        $coreListing = new Customer\Listing();
        $coreListing
            ->setOrderKey('o_id')
            ->setOrder('ASC');

        $listing = new Listing($coreListing);

        $this->addListingFilters($listing, $filters);

        // dump($listing->getListing()->getQuery()->__toString());

        return $listing;
    }

    /**
     * @param Listing $listing
     * @param array $filters
     */
    protected function addListingFilters(Listing $listing, array $filters = [])
    {
        $equalsProperties = [
            'id'     => 'o_id',
            'active' => 'active',
        ];

        $searchProperties = [
            'email' => 'email',
            'name'  => [
                'name',
                'firstname',
                'surname',
                'userName'
            ]
        ];

        foreach ($equalsProperties as $property => $databaseField) {
            if (array_key_exists($property, $filters)) {
                $listing->addFilter(new Filter\Equals($databaseField, $filters[$property]));
            }
        }

        foreach ($searchProperties as $property => $databaseField) {
            if (array_key_exists($property, $filters)) {
                $listing->addFilter(new Filter\Search($databaseField, $filters[$property]));
            }
        }

        if (array_key_exists('gender', $filters)) {
            /** @var \Pimcore\Model\Object\CustomerSegmentGroup $segmentGroup */
            $segmentGroup = \Pimcore\Model\Object\CustomerSegmentGroup::getByName('gender', 1);

            $segments = [];
            foreach ($filters['gender'] as $genderId) {
                $segments[] = CustomerSegment::getById($genderId);
            }

            /*
            dump($segmentGroup->getName());
            dump(array_map(function (CustomerSegment $segment) {
                return $segment->getName();
            }, $segments));
            */

            $segmentFilter = new Filter\CustomerSegment($segmentGroup, $segments);
            $listing->addFilter($segmentFilter);
        }
    }

    /**
     * Fetch filters and set them on view
     *
     * @return array
     */
    protected function fetchListFilters()
    {
        /** @var \Zend_Controller_Action $this */
        $filters = $this->getParam('filter', []);
        $this->view->filters = $filters;

        return $filters;
    }
}
