<?php

namespace CustomerManagementFrameworkBundle\Authentication\Sso;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use Symfony\Component\HttpFoundation\Request;

interface ExternalAuthHandlerInterface
{
    /**
     * Handle authentication against external service
     *
     * @param Request $request
     */
    public function authenticate(Request $request);

    /**
     * Try to load customer from authentication response
     *
     * @param Request $request
     * @return CustomerInterface
     */
    public function getCustomerFromAuthResponse(Request $request);

    /**
     * Update customer object from authentication response (create SsoIdentity entry, add data from user profile)
     *
     * @param CustomerInterface $customer
     * @param Request $request
     * @return SsoIdentityInterface
     */
    public function updateCustomerFromAuthResponse(CustomerInterface $customer, Request $request);
}
