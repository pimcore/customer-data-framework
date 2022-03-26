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

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractCustomerSaveHandler implements CustomerSaveHandlerInterface
{
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerInterface|null
     *
     * TODO: Rename to originalCustomer
     */
    protected $orginalCustomer;

    /**
     * If this returns true, the CustomerSaveHandler is provided with an original instance of the customer how it looks like in the database.
     * This could be useful i.e. to compare if a field has changed. If the original customer is not needed this should return false to improve performance.
     *
     * @return bool
     */
    public function isOriginalCustomerNeeded()
    {
        return false;
    }

    /**
     * Returns the original customer object from the database. self::isOriginalCustomerNeeded() need to return true if this feature is needed.
     *
     * @return CustomerInterface|null
     */
    public function getOriginalCustomer()
    {
        return $this->orginalCustomer;
    }

    /**
     * setter for the originalCustomer property
     *
     * @param CustomerInterface|null $originalCustomer
     *
     * @return void
     */
    public function setOriginalCustomer(CustomerInterface $originalCustomer = null)
    {
        $this->orginalCustomer = $originalCustomer;
    }

    /**
     * called in preAdd and preUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
    }

    /**
     * called in postAdd and postUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function postSave(CustomerInterface $customer)
    {
    }

    /**
     * called in preAdd hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preAdd(CustomerInterface $customer)
    {
    }

    /**
     * called in postAdd hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function postAdd(CustomerInterface $customer)
    {
    }

    /**
     * called in preUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preUpdate(CustomerInterface $customer)
    {
    }

    /**
     * called in postUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function postUpdate(CustomerInterface $customer)
    {
    }

    /**
     * called in preDelete hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preDelete(CustomerInterface $customer)
    {
    }

    /**
     * called in postDelete hook of customer objects
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function postDelete(CustomerInterface $customer)
    {
    }
}
