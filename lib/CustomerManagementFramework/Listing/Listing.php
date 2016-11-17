<?php

namespace CustomerManagementFramework\Listing;

use Pimcore\Model\Object\Listing as CoreListing;

/**
 * Generic wrapper for object listings with support for reusable filter classes
 */
class Listing
{
    const DEFAULT_PAGE_SIZE = 25;

    /**
     * @var CoreListing\Concrete|CoreListing\Dao
     */
    protected $listing;

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    /**
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     */
    public function __construct(CoreListing\Concrete $listing)
    {
        $this->listing = $listing;
    }

    /**
     * @return CoreListing\Concrete|CoreListing\Dao
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param FilterInterface[] $filters
     */
    public function addFilters(array $filters)
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    /**
     * @param FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        $this->setFilterCallback();

        if ($filter instanceof ListingFilterInterface) {
            $filter->applyToListing($this->listing);
        }
    }

    /**
     * Apply filters to select
     */
    protected function setFilterCallback()
    {
        $this->listing->onCreateQuery(function(\Zend_Db_Select $query) {
            foreach ($this->filters as $filter) {
                if ($filter instanceof OnCreateQueryFilterInterface) {
                    $filter->applyOnCreateQuery($this->listing, $query);
                }
            }
        });
    }
}
