<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

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
