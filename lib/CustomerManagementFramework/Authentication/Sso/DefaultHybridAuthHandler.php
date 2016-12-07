<?php

namespace CustomerManagementFramework\Authentication\Sso;

use CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFramework\Encryption\EncryptionServiceInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\OAuth\OAuth1TokenInterface;
use CustomerManagementFramework\Model\OAuth\OAuth2TokenInterface;
use CustomerManagementFramework\Model\OAuth\OAuthTokenInterface;
use CustomerManagementFramework\Model\SsoIdentityInterface;
use Pimcore\Model\Object\Objectbrick\Data\OAuth1Token;
use Pimcore\Model\Object\Objectbrick\Data\OAuth2Token;
use Pimcore\Model\Object\SsoIdentity;
use Pimcore\Tool\HybridAuth;

class DefaultHybridAuthHandler implements ExternalAuthHandlerInterface
{
    /**
     * @var SsoIdentityServiceInterface
     */
    protected $ssoIdentityService;

    /**
     * @var EncryptionServiceInterface
     */
    protected $encryptionService;

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
     * @param EncryptionServiceInterface $encryptionService
     */
    public function __construct(SsoIdentityServiceInterface $ssoIdentityService, EncryptionServiceInterface $encryptionService)
    {
        $this->ssoIdentityService = $ssoIdentityService;
        $this->encryptionService  = $encryptionService;
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

        $this->applyCredentialsToSsoIdentity($ssoIdentity);
        $this->ssoIdentityService->addSsoIdentity($customer, $ssoIdentity);

        $this->applyProfileToCustomer($customer);

        return $ssoIdentity;
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     */
    protected function applyCredentialsToSsoIdentity(SsoIdentityInterface $ssoIdentity)
    {
        $wrappedAdapter = $this->getAdapter()->adapter;
        if ($wrappedAdapter instanceof \Hybrid_Provider_Model_OAuth1) {
            $this->applyOAuth1Credentials($ssoIdentity);
        } else if ($this->getAdapter()->adapter instanceof \Hybrid_Provider_Model_OAuth2) {
            $this->applyOAuth2Credentials($ssoIdentity);
        }
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     */
    protected function applyOAuth1Credentials(SsoIdentityInterface $ssoIdentity)
    {
        $credentials = $ssoIdentity->getCredentials();

        /** @var OAuth1TokenInterface $token */
        $token = $credentials->getOAuth1Token();
        if (!$token) {
            $token = new OAuth1Token($ssoIdentity);
            $credentials->setOAuth1Token($token);
        }

        // see https://tools.ietf.org/html/rfc5849#section-2.3
        $this->addTokenData($token, [
            'access_token'        => 'token',
            'access_token_secret' => 'tokenSecret',
        ]);
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     */
    protected function applyOAuth2Credentials(SsoIdentityInterface $ssoIdentity)
    {
        $credentials = $ssoIdentity->getCredentials();

        /** @var OAuth2TokenInterface $token */
        $token = $credentials->getOAuth2Token();
        if (!$token) {
            $token = new OAuth2Token($ssoIdentity);
            $credentials->setOAuth2Token($token);
        }

        // see https://tools.ietf.org/html/rfc6749#section-5.1
        // TODO get scope and token_type from response
        $this->addTokenData($token, [
            'access_token'  => 'accessToken',
            'refresh_token' => 'refreshToken',
            'expires_at'    => 'expiresAt'
        ]);
    }

    /**
     * Add token data from HA access token to token object/objectbrick. Optionally encrypts value if it is defined as secure
     *
     * @param OAuthTokenInterface $token
     * @param array $mapping
     */
    protected function addTokenData(OAuthTokenInterface $token, array $mapping)
    {
        $accessToken = $this->getAdapter()->getAccessToken();

        foreach ($mapping as $field => $property) {
            if (!isset($accessToken[$field])) {
                throw new \RuntimeException(sprintf(
                    'Unable to find field %s on access token data. Existing fields: %s',
                    $field,
                    implode(', ', array_keys($accessToken))
                ));
            }

            $setter = 'set' . ucfirst($property);
            if (!method_exists($token, $setter)) {
                throw new \RuntimeException(sprintf('Can\'t apply property %s on token as method %s does not exist.', $property, $setter));
            }

            $secure = false;
            if (in_array($property, $token->getSecureProperties())) {
                $secure = true;
            }

            $value = $accessToken[$field];
            if ($secure) {
                $value = $this->encryptionService->encrypt($value);
            }

            $token->$setter($value);
        }
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
