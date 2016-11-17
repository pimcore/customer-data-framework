<?php

namespace CustomerManagementFramework\Listing;

use Pimcore\Model\Object\Listing as CoreListing;

interface OnCreateQueryFilterInterface extends FilterInterface
{
    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param \Zend_Db_Select $query
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, \Zend_Db_Select $query);
}
