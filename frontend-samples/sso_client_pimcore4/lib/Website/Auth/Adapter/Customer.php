<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace Website\Auth\Adapter;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\Password;
use Pimcore\Model\DataObject\Customer as CustomerModel;
use Zend_Auth_Adapter_Exception;
use Zend_Auth_Result;

/**
 * Standard Zend_Auth adapter looking up customers through the CMF CustomerProvider and verifying against a 'password' property.
 */
class Customer implements \Zend_Auth_Adapter_Interface
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @param string $email
     * @param string $password
     */
    public function __construct($email, $password)
    {
        $this->email    = $email;
        $this->password = $password;
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     *
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $list = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();
        $list->addConditionParam('email = ?', $this->email);
        $list->setLimit(1);

        /** @var CustomerInterface|CustomerModel $customer */
        $customer = null;
        if ($list->count() === 1) {
            $customer = $list->getItems(0, 0)[0];
        }

        if (!$customer) {
            return new \Zend_Auth_Result(
                \Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                $this->email
            );
        }

        // user was found but account is inactive
        if (!$customer->getActive()) {
            return new \Zend_Auth_Result(
                \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                $this->email
            );
        }

        // verify password
        if ($this->verifyPassword($customer)) {
            return new \Zend_Auth_Result(
                \Zend_Auth_Result::SUCCESS,
                $customer
            );
        }

        // password verification failed -> return failure state
        return new \Zend_Auth_Result(
            \Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
            $this->email
        );
    }

    /**
     * Check supplied password
     *
     * @param CustomerModel $customer
     *
     * @return bool
     */
    protected function verifyPassword(CustomerModel $customer)
    {
        /* @var Password $passwordField */
        $passwordField = $customer->getClass()->getFieldDefinition('password');

        return $passwordField->verifyPassword($this->password, $customer, true);
    }
}
