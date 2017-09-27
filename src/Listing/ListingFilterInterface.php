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

use Pimcore\Model\DataObject\Listing as CoreListing;

interface ListingFilterInterface extends FilterInterface
{
    /**
     * Apply filter to listing
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     */
    public function applyToListing(CoreListing\Concrete $listing);
}
