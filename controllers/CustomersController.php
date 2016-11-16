<?php

use CustomerManagementFramework\Controller\Admin;
use CustomerManagementFramework\Controller\Traits\PaginatorController;
use CustomerManagementFramework\Listing\Filter\Equals;
use CustomerManagementFramework\Listing\Filter\Search;
use CustomerManagementFramework\Listing\Listing;
use Pimcore\Model\Object\Customer;

class CustomerManagementFramework_CustomersController extends Admin
{
    use PaginatorController;

    public function listAction()
    {
        $this->enableLayout();

        $filters   = $this->fetchListFilters();
        $listing   = $this->buildListing($filters);
        $paginator = $this->buildPaginator($listing->getListing());

        $this->view->paginator = $paginator;
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
        ];

        foreach ($equalsProperties as $property => $databaseField) {
            if (array_key_exists($property, $filters)) {
                $listing->addFilter(new Equals($databaseField, $filters[$property]));
            }
        }

        foreach ($searchProperties as $property => $databaseField) {
            if (array_key_exists($property, $filters)) {
                $listing->addFilter(new Search($databaseField, $filters[$property]));
            }
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
