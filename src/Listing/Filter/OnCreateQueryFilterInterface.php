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

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use CustomerManagementFrameworkBundle\Listing\FilterInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;

interface OnCreateQueryFilterInterface extends FilterInterface
{
    /**
     * Apply filter directly to query
     *
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder);
}
