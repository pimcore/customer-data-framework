<?php

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use CustomerManagementFrameworkBundle\Listing\FilterInterface;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\Object\Listing as CoreListing;

interface OnCreateQueryFilterInterface extends FilterInterface
{
    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param QueryBuilder $query
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $query);
}
