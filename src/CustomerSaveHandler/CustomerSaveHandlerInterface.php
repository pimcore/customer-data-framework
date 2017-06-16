<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface CustomerSaveHandlerInterface
{
    public function __construct($config, LoggerInterface $logger);

    /**
     * If this returns true, the CustomerSaveHandler is provided with an original instance of the customer how it looks like in the database.
     * This could be useful i.e. to compare if a field has changed. If the original customer is not needed this should return false to improve performance.
     *
     * @return bool
     */
    public function isOriginalCustomerNeeded();

    /**
     * Returns the original customer object from the database. self::isOriginalCustomerNeeded() need to return true if this feature is needed.
     *
     * @return CustomerInterface
     */
    public function getOriginalCustomer();

    /**
     * setter for the originalCustomer property
     *
     * @param CustomerInterface $originalCustomer
     * @return void
     */
    public function setOriginalCustomer(CustomerInterface $originalCustomer);

    /**
     * called in preAdd and preUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function preSave(CustomerInterface $customer);

    /**
     * called in postAdd and postUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function postSave(CustomerInterface $customer);

    /**
     * called in preAdd hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function preAdd(CustomerInterface $customer);

    /**
     * called in postAdd hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function postAdd(CustomerInterface $customer);

    /**
     * called in preUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function preUpdate(CustomerInterface $customer);

    /**
     * called in postUpdate hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function postUpdate(CustomerInterface $customer);

    /**
     * called in preDelete hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function preDelete(CustomerInterface $customer);

    /**
     * called in postDelete hook of customer objects
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function postDelete(CustomerInterface $customer);

}