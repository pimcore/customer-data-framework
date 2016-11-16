<?php

use CustomerManagementFramework\Controller\Admin;
use CustomerManagementFramework\Controller\Traits\PaginatorController;
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
