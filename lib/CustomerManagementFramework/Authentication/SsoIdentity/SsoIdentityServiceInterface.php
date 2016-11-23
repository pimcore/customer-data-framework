<?php
namespace CustomerManagementFramework\Authentication\SsoIdentity;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\SsoIdentityInterface;

interface SsoIdentityServiceInterface
{
    /**
     * @param CustomerInterface $customer
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(CustomerInterface $customer);

    /**
     * @param string $provider
     * @param string $identifier
     * @return CustomerInterface|null
     */
    public function getCustomerBySsoIdentity($provider, $identifier);

    /**
     * @param CustomerInterface $customer
     * @param string $provider
     * @param string $identifier
     * @return SsoIdentityInterface|null
     */
    public function getSsoIdentity(CustomerInterface $customer, $provider, $identifier);

    /**
     * @param CustomerInterface $customer
     * @param SsoIdentityInterface $ssoIdentity
     * @return $this
     */
    public function addSsoIdentity(CustomerInterface $customer, SsoIdentityInterface $ssoIdentity);

    /**
     * @param string $provider
     * @param string $identifier
     * @param mixed $profileData
     * @return SsoIdentityInterface
     */
    public function createSsoIdentity($provider, $identifier, $profileData);
}
