<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
