<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use CustomerManagementFrameworkBundle\Listing\FilterInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;

interface OnCreateQueryFilterInterface extends FilterInterface
{
    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete $listing
     * @param QueryBuilder $queryBuilder
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder);
}
