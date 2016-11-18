<?php

use CustomerManagementFramework\Controller\Admin;
use CustomerManagementFramework\Controller\Traits\PaginatorController;
use CustomerManagementFramework\Listing\Filter;
use CustomerManagementFramework\Listing\Listing;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
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

    /**
     * Load a segment group and set it on the view
     *
     * @param $name
     */
    protected function loadSegmentGroup($name)
    {
        /** @var \Pimcore\Model\Object\CustomerSegmentGroup $group */
        $segmentGroup = \Pimcore\Model\Object\CustomerSegmentGroup::getByName($name, 1);
        if (!$segmentGroup) {
            throw new InvalidArgumentException(sprintf('Segment group %s was not found', $name));
        }

        if (!isset($this->view->segmentGroups)) {
            $this->view->segmentGroups = [];
        }

        $this->view->segmentGroups[] = $segmentGroup;
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

        $prefilteredSegment = $this->fetchPrefilteredSegment();
        if (null !== $prefilteredSegment) {
            $listing->addFilter(new Filter\CustomerSegment($prefilteredSegment->getGroup(), [$prefilteredSegment]));

            $this->view->prefilteredSegment = $prefilteredSegment;
        }

        if (array_key_exists('segments', $filters)) {
            foreach ($filters['segments'] as $groupId => $segmentIds) {
                if (null !== $prefilteredSegment && $prefilteredSegment->getGroup()->getId() === $groupId) {
                    continue;
                }

                /** @var \Pimcore\Model\Object\CustomerSegmentGroup $segmentGroup */
                $segmentGroup = \Pimcore\Model\Object\CustomerSegmentGroup::getById($groupId);
                if (!$segmentGroup) {
                    throw new InvalidArgumentException(sprintf('Segment group %d was not found', $groupId));
                }


                $segments = [];
                foreach ($segmentIds as $prefilteredSegmentId) {
                    $segment = CustomerSegment::getById($prefilteredSegmentId);

                    if (!$segment) {
                        throw new InvalidArgumentException(sprintf('Segment %d was not found', $prefilteredSegmentId));
                    }

                    $segments[] = $segment;
                }

                $listing->addFilter(new Filter\CustomerSegment($segmentGroup, $segments));
            }
        }
    }

    /**
     * @return CustomerSegmentInterface|null
     */
    protected function fetchPrefilteredSegment()
    {
        $segmentId = $this->getParam('segmentId');

        if ($segmentId) {
            $segment = CustomerSegment::getById($segmentId);
            if (!$segment) {
                throw new InvalidArgumentException(sprintf('Segment %d was not found', $segmentId));
            }

            return $segment;
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
