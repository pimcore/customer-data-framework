<?php

namespace CustomerManagementFramework\Listing;

use Pimcore\Model\Object\Listing as CoreListing;

interface OnCreateQueryFilterInterface extends FilterInterface
{
    /**
     * Apply filter to query
     *
     * @param \Zend_Db_Select $query
     * @param int $classId
     * @return
     * @internal param CoreListing\Concrete|CoreListing\Dao $listing
     */
    public function applyOnCreateQuery(\Zend_Db_Select $query, $classId);
}
