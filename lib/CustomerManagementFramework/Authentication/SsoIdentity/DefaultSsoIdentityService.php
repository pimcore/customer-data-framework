<?php

namespace CustomerManagementFramework\Authentication\SsoIdentity;

use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\SsoAwareCustomerInterface;
use CustomerManagementFramework\Model\SsoIdentityInterface;
use Pimcore\Db;
use Pimcore\Model\Object\Fieldcollection\Data\SsoIdentity;

/**
 * SSO identity service handling SsoIdentities as FieldCollection on the Customer object
 */
class DefaultSsoIdentityService implements SsoIdentityServiceInterface
{
    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(CustomerProviderInterface $customerProvider)
    {
        $this->customerProvider = $customerProvider;
    }

    /**
     * @param string $provider
     * @param string $identifier
     * @return CustomerInterface|null
     */
    public function getCustomerBySsoIdentity($provider, $identifier)
    {
        return null;

        $db    = Db::get();
        $query = $db
            ->select()
            ->columns(['o_id'])
            ->from('object_collection_SsoIdentity_%d', $this->customerProvider->getCustomerClassId())
            ->where('provider = ?', $provider)
            ->where('identifier = ?', $identifier);

        $result = $db
            ->prepare($query)
            ->fetchAll();

        if (count($result) === 1) {
            return $this->customerProvider->getById((int)$result[0]['o_id']);
        }
    }

    /**
     * @param CustomerInterface|SsoAwareCustomerInterface $customer
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(CustomerInterface $customer)
    {
        $this->checkCustomer($customer);

        $identities = [];
        if (empty($customer->getSsoIdentities())) {
            return $identities;
        }

        foreach ($customer->getSsoIdentities() as $ssoIdentity) {
            $identities[] = $ssoIdentity;
        }

        return $identities;
    }

    /**
     * @param CustomerInterface $customer
     * @param string $provider
     * @param string $identifier
     * @return SsoIdentityInterface|null
     */
    public function getSsoIdentity(CustomerInterface $customer, $provider, $identifier)
    {
        foreach ($this->getSsoIdentities($customer) as $ssoIdentity) {
            if ($ssoIdentity->getProvider() === $provider && $ssoIdentity->getIdentifier() === $identifier) {
                return $ssoIdentity;
            }
        }
    }

    /**
     * @param CustomerInterface|SsoAwareCustomerInterface $customer
     * @param SsoIdentityInterface $ssoIdentity
     * @return $this
     */
    public function addSsoIdentity(CustomerInterface $customer, SsoIdentityInterface $ssoIdentity)
    {
        $this->checkCustomer($customer);

        $ssoIdentities   = $this->getSsoIdentities($customer);
        $ssoIdentities[] = $ssoIdentity;

        $customer->setSsoIdentities($ssoIdentities);
    }

    /**
     * @param string $provider
     * @param string $identifier
     * @param mixed $profileData
     * @return SsoIdentityInterface
     */
    public function createSsoIdentity($provider, $identifier, $profileData)
    {
        $ssoIdentity = new SsoIdentity();
        $ssoIdentity->setProvider($provider);
        $ssoIdentity->setIdentifier($identifier);
        $ssoIdentity->setProfileData($profileData);

        return $ssoIdentity;
    }

    /**
     * @param CustomerInterface $customer
     */
    protected function checkCustomer(CustomerInterface $customer)
    {
        if (!$customer instanceof SsoAwareCustomerInterface) {
            throw new \RuntimeException('Customer needs to implement SsoAwareCustomerInterface');
        }
    }
}
