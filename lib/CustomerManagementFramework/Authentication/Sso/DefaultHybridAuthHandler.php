<?php

namespace CustomerManagementFramework\Authentication\Sso;

use CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\SsoIdentityInterface;
use Pimcore\Tool\HybridAuth;

class DefaultHybridAuthHandler implements ExternalAuthHandlerInterface
{
    /**
     * @var SsoIdentityServiceInterface
     */
    protected $ssoIdentityService;

    /**
     * @var bool
     */
    protected $authenticated = false;

    /**
     * @var \Hybrid_Provider_Adapter
     */
    protected $adapter;

    /**
     * @var \Hybrid_User_Profile $userProfile
     */
    protected $userProfile;

    /**
     * @param SsoIdentityServiceInterface $ssoIdentityService
     */
    public function __construct(SsoIdentityServiceInterface $ssoIdentityService)
    {
        $this->ssoIdentityService = $ssoIdentityService;
    }

    /**
     * @return \Hybrid_Provider_Adapter
     */
    public function getAdapter()
    {
        $this->checkAuthenticated();

        return $this->adapter;
    }

    /**
     * @return \Hybrid_User_Profile
     */
    public function getUserProfile()
    {
        $this->checkAuthenticated();

        return $this->userProfile;
    }

    /**
     * Handle authentication against external service
     *
     * @param \Zend_Controller_Request_Http $request
     */
    public function authenticate(\Zend_Controller_Request_Http $request)
    {
        $provider = $request->getParam('provider');
        if (empty($provider)) {
            throw new \InvalidArgumentException('Need a provider to authenticate with');
        }

        $this->adapter = HybridAuth::authenticate($provider);
        if (!$this->adapter || !($this->adapter instanceof \Hybrid_Provider_Adapter)) {
            throw new \RuntimeException(sprintf('Failed to authenticate with adapter for provider "%s"', htmlentities($provider)));
        }

        $this->userProfile = $this->adapter->getUserProfile();
        if (!$this->userProfile || !($this->userProfile instanceof \Hybrid_User_Profile)) {
            throw new \RuntimeException(sprintf('Failed to load user profile for provider "%s"', htmlentities($provider)));
        }

        $this->authenticated = true;
    }

    /**
     * Try to load customer from authentication response
     *
     * @param \Zend_Controller_Request_Http $request
     * @return CustomerInterface
     */
    public function getCustomerFromAuthResponse(\Zend_Controller_Request_Http $request)
    {
        return $this->ssoIdentityService->getCustomerBySsoIdentity(
            $this->getProviderId(),
            $this->getProfileIdentifier()
        );
    }

    /**
     * Update customer object from authentication response (create SsoIdentity entry, add data from user profile)
     *
     * @param CustomerInterface $customer
     * @param \Zend_Controller_Request_Http $request
     * @return SsoIdentityInterface
     */
    public function updateCustomerFromAuthResponse(CustomerInterface $customer, \Zend_Controller_Request_Http $request)
    {
        $this->checkAuthenticated();

        $ssoIdentity = $this->ssoIdentityService->getSsoIdentity(
            $customer,
            $this->getProviderId(),
            $this->getProfileIdentifier()
        );

        if (null !== $ssoIdentity) {
            throw new \RuntimeException(sprintf(
                'Customer has already an SSO identity for provider %s and identifier %s',
                $this->getProviderId(),
                $this->getProfileIdentifier()
            ));
        }

        $ssoIdentity = $this->ssoIdentityService->createSsoIdentity(
            $customer,
            $this->getProviderId(),
            $this->getProfileIdentifier(),
            json_encode($this->userProfile)
        );

        $this->ssoIdentityService->addSsoIdentity($customer, $ssoIdentity);
        $this->applyProfileToCustomer($customer);

        return $ssoIdentity;
    }

    /**
     * @param CustomerInterface $customer
     */
    protected function applyProfileToCustomer(CustomerInterface $customer)
    {
        $userProfile = $this->userProfile;

        $properties = [
            'email'       => $userProfile->emailVerified ? $userProfile->email : null,
            'gender'      => $userProfile->gender,
            'firstname'   => $userProfile->firstName,
            'lastname'    => $userProfile->lastName,
            'street'      => $userProfile->address,
            'zip'         => $userProfile->zip,
            'city'        => $userProfile->city,
            'countryCode' => $userProfile->country,
            'phone'       => $userProfile->phone,
        ];

        foreach ($properties as $property => $value) {
            $this->setIfEmpty($customer, $property, $value);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param string $property
     * @param mixed $value
     * @return $this
     */
    protected function setIfEmpty(CustomerInterface $customer, $property, $value = null)
    {
        $getter = 'get' . ucfirst($property);
        $setter = 'set' . ucfirst($property);

        if (!empty($value) && empty($customer->$getter())) {
            $customer->$setter($value);
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function getProviderId()
    {
        $this->checkAuthenticated();

        return $this->adapter->adapter->providerId;
    }

    /**
     * @return string
     */
    protected function getProfileIdentifier()
    {
        $this->checkAuthenticated();

        return $this->userProfile->identifier;
    }

    /**
     * Check if we're authenticated
     */
    protected function checkAuthenticated()
    {
        if (!$this->authenticated) {
            throw new \RuntimeException('No auth response found. Please authenticate first.');
        }
    }
}
