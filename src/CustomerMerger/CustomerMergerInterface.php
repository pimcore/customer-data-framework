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

namespace CustomerManagementFrameworkBundle\CustomerMerger;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface CustomerMergerInterface
{
    /**
     * Adds all values from source customer to target customer and returns merged target customer instance.
     * Afterwards the source customer will be set to inactive and unpublished.
     *
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @param bool $mergeAttributes
     *
     * @return CustomerInterface
     */
    public function mergeCustomers(
        CustomerInterface $sourceCustomer,
        CustomerInterface $targetCustomer,
        $mergeAttributes = true
    );
}
