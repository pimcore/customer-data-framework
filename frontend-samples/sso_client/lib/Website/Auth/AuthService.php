<?php

namespace Website\Auth;

use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Tool\HybridAuth;

/**
 * Responsible for managing logged in state
 */
class AuthService
{
    /**
     * @return \Zend_Auth
     */
    public function getAuth()
    {
        return \Zend_Auth::getInstance();
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->getAuth()->hasIdentity();
    }

    /**
     * @return CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = null;
        if ($this->isLoggedIn()) {
            $customer = Factory::getInstance()
                ->getCustomerProvider()
                ->getById($this->getAuth()->getIdentity());

            if (!$customer) {
                throw new \RuntimeException('Failed to load logged in customer from customer provider');
            }
        }

        return $customer;
    }

    /**
     * @param CustomerInterface $customer
     * @return $this
     */
    public function login(CustomerInterface $customer)
    {
        // mitigate session fixation attacks
        \Zend_Session::regenerateId();
        $this->getAuth()->getStorage()->write($customer->getId());

        return $this;
    }

    /**
     * @return $this
     */
    public function logout()
    {
        $this->getAuth()->clearIdentity();

        // initialize config and log out all providers
        // HA maintains an own session and we want to clear all previous logins
        HybridAuth::initializeHybridAuth();
        \Hybrid_Auth::logoutAllProviders();

        // mitigate session fixation attacks
        \Zend_Session::regenerateId();

        return $this;
    }
}
