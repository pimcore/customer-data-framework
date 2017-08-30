<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Object\CustomerSegmentGroup;

interface NewsletterProviderHandlerInterface
{
    /**
     * update customer in mail provider
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function updateCustomer(CustomerInterface $customer);

    /**
     * update customer in mail provider when email address has changed
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function updateCustomerEmail(CustomerInterface $customer, $oldEmail);

    /**
     * delete customer in mail provider
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function deleteCustomer();

    /**
     * @param CustomerSegmentGroup[] $groups
     * @return void
     */
    public function updateSegmentGroups(array $groups);
}