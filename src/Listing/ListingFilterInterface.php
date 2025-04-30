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

use Pimcore\Model\DataObject\Listing as CoreListing;

interface ListingFilterInterface extends FilterInterface
{
    /**
     * Apply filter to listing
     *
     */
    public function applyToListing(CoreListing\Concrete $listing);
}
