<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Listing;

use CustomerManagementFrameworkBundle\Listing\Filter\OnCreateQueryFilterInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;

/**
 * Adds support for reusable filter classes which can be applied on a listing through this handler
 */
class FilterHandler
{
    /**
     * @var CoreListing\Concrete
     */
    protected $listing;

    /**
     * @var FilterInterface[]
     */
    protected $filters = [];

    public function __construct(CoreListing\Concrete $listing)
    {
        $this->listing = $listing;
    }

    /**
     * @return CoreListing\Concrete
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

    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        $this->setFilterCallback();

        if ($filter instanceof ListingFilterInterface) {
            $filter->applyToListing($this->listing);
        }
    }

    protected function setFilterCallback()
    {
        $this->listing->onCreateQueryBuilder(
            function (QueryBuilder $queryBuilder) {
                foreach ($this->filters as $filter) {
                    if ($filter instanceof OnCreateQueryFilterInterface) {
                        $filter->applyOnCreateQuery($this->listing, $queryBuilder);
                    }
                }
            }
        );
    }
}
