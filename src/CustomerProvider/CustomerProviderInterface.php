<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface CustomerProviderInterface
{
    /**
     * @return int
     */
    public function getCustomerClassId();

    /**
     * @return string
     */
    public function getCustomerClassName();

    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getList();

    /**
     * Create a customer instance
     *
     * @param array $data
     *
     * @return CustomerInterface
     */
    public function create(array $data = []);

    /**
     * Update a customer instance
     *
     * @param CustomerInterface $customer
     * @param array $data
     *
     * @return CustomerInterface
     */
    public function update(CustomerInterface $customer, array $data = []);

    /**
     * Delete a customer instance
     *
     * @param CustomerInterface $customer
     *
     * @return $this
     */
    public function delete(CustomerInterface $customer);

    /**
     * Get customer by ID
     *
     * @param int $id
     * @param bool $foce
     *
     * @return CustomerInterface|null
     */
    public function getById($id, $force = false);

    /**
     * Sets the correct parent folder and object key for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function applyObjectNamingScheme(CustomerInterface $customer);
}
