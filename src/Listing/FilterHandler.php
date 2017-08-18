<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Listing;

use CustomerManagementFrameworkBundle\Listing\Filter\OnCreateQueryFilterInterface;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\Object\Listing as CoreListing;

/**
 * Adds support for reusable filter classes which can be applied on a listing through this handler
 */
class FilterHandler
{
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
        $this->listing->onCreateQuery(
            function (QueryBuilder $query) {
                foreach ($this->filters as $filter) {
                    if ($filter instanceof OnCreateQueryFilterInterface) {
                        $filter->applyOnCreateQuery($this->listing, $query);
                    }
                }
            }
        );
    }
}
