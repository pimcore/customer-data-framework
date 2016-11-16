<?php

namespace CustomerManagementFramework\Listing;

use Pimcore\Model\Object\Listing as CoreListing;

interface ListingFilterInterface extends FilterInterface
{
    /**
     * Apply filter to listing
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     */
    public function applyToListing(CoreListing\Concrete $listing);
}
