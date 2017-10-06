<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use CustomerManagementFrameworkBundle\Listing\FilterInterface;
use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;

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
