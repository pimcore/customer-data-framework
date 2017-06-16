<?php

namespace CustomerManagementFrameworkBundle\Authentication\Sso;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;

interface ExternalAuthHandlerInterface
{
    /**
     * Handle authentication against external service
     *
     * @param \Zend_Controller_Request_Http $request
     */
    public function authenticate(\Zend_Controller_Request_Http $request);

    /**
     * Try to load customer from authentication response
     *
     * @param \Zend_Controller_Request_Http $request
     * @return CustomerInterface
     */
    public function getCustomerFromAuthResponse(\Zend_Controller_Request_Http $request);

    /**
     * Update customer object from authentication response (create SsoIdentity entry, add data from user profile)
     *
     * @param CustomerInterface $customer
     * @param \Zend_Controller_Request_Http $request
     * @return SsoIdentityInterface
     */
    public function updateCustomerFromAuthResponse(CustomerInterface $customer, \Zend_Controller_Request_Http $request);
}
