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

namespace CustomerManagementFrameworkBundle\CustomerProvider;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\Concrete;

/**
 * @method string getParentPath()
 */
interface CustomerProviderInterface
{
    /**
     * @return string
     */
    public function getCustomerClassId();

    /**
     * @return string
     */
    public function getCustomerClassName();

    /**
     * Get an object listing
     *
     * @return \Pimcore\Model\DataObject\Listing\Concrete
     */
    public function getList();

    /**
     * Create a published customer instance and sets it's values from given data
     *
     * @param array $data
     *
     * @return CustomerInterface
     */
    public function create(array $data = []);

    /**
     * Create a customer instance
     *
     * @return Concrete&CustomerInterface
     */
    public function createCustomerInstance();

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
     * @param bool $force
     *
     * @return CustomerInterface|null
     */
    public function getById($id, $force = false);

    /**
     * Get active customer by email
     *
     * @param string $email
     *
     * @return CustomerInterface|null
     *
     * @throws \RuntimeException
     */
    public function getActiveCustomerByEmail($email);

    /**
     * Sets the correct parent folder and object key for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function applyObjectNamingScheme(CustomerInterface $customer);

    /**
     * @return string
     *
     * @deprecated use getParentPath() instead.
     */
    public function getParentParentPath();

    /**
     * @param string $parentPath
     *
     * @return void
     */
    public function setParentPath($parentPath);

    /**
     * @param \Pimcore\Model\DataObject\Listing\Concrete $list
     *
     * @return void
     */
    public function addActiveCondition($list);

    /**
     * @param \Pimcore\Model\DataObject\Listing\Concrete $list
     *
     * @return void
     */
    public function addInActiveCondition($list);
}
