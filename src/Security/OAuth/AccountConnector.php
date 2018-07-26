<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Security\OAuth;

use CustomerManagementFrameworkBundle\Encryption\EncryptionServiceInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\OAuth\OAuth1TokenInterface;
use CustomerManagementFrameworkBundle\Model\OAuth\OAuth2TokenInterface;
use CustomerManagementFrameworkBundle\Model\OAuth\OAuthTokenInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use CustomerManagementFrameworkBundle\Security\SsoIdentity\SsoIdentityServiceInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth2ResourceOwner;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Pimcore\Model\DataObject\Objectbrick\Data\OAuth1Token;
use Pimcore\Model\DataObject\Objectbrick\Data\OAuth2Token;
use Pimcore\Model\DataObject\SsoIdentity;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountConnector implements AccountConnectorInterface
{
    /**
     * @var SsoIdentityServiceInterface
     */
    private $ssoIdentityService;

    /**
     * @var EncryptionServiceInterface
     */
    private $encryptionService;

    public function __construct(
        SsoIdentityServiceInterface $ssoIdentityService,
        EncryptionServiceInterface $encryptionService
    ) {
        $this->ssoIdentityService = $ssoIdentityService;
        $this->encryptionService = $encryptionService;
    }

    public function connectToSsoIdentity(UserInterface $user, UserResponseInterface $response): SsoIdentityInterface
    {
        if (!$user instanceof CustomerInterface) {
            throw new \InvalidArgumentException('User is not supported');
        }

        $provider = $response->getResourceOwner()->getName();
        $identifier = $response->getUsername();

        $ssoIdentity = $this->ssoIdentityService->getSsoIdentity(
            $user,
            $provider,
            $identifier
        );

        if (null !== $ssoIdentity) {
            throw new \RuntimeException(
                sprintf(
                    'Customer has already an SSO identity for provider %s and identifier %s',
                    $provider,
                    $identifier
                )
            );
        }

        $ssoIdentity = $this->ssoIdentityService->createSsoIdentity(
            $user,
            $provider,
            $identifier,
            json_encode($response->getData())
        );

        $this->applyCredentialsToSsoIdentity($ssoIdentity, $response);
        $this->applyProfileToCustomer($user, $response);

        $this->ssoIdentityService->addSsoIdentity($user, $ssoIdentity);

        return $ssoIdentity;
    }

    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $this->connectToSsoIdentity($user, $response);
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     * @param UserResponseInterface $response
     */
    protected function applyCredentialsToSsoIdentity(SsoIdentityInterface $ssoIdentity, UserResponseInterface $response)
    {
        $resourceOwner = $response->getResourceOwner();

        if ($resourceOwner instanceof GenericOAuth1ResourceOwner) {
            $this->applyOAuth1Credentials($ssoIdentity, $response);
        } elseif ($resourceOwner instanceof GenericOAuth2ResourceOwner) {
            $this->applyOAuth2Credentials($ssoIdentity, $response);
        } else {
            throw new \RuntimeException(sprintf('Unsupported OAuth resource owner %s', get_class($resourceOwner)));
        }
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     * @param UserResponseInterface $response
     */
    protected function applyOAuth1Credentials(SsoIdentityInterface $ssoIdentity, UserResponseInterface $response)
    {
        /** @var SsoIdentity\Credentials $credentials */
        $credentials = $ssoIdentity->getCredentials();

        /** @var OAuth1TokenInterface $token */
        $token = $credentials->getOAuth1Token();
        if (!$token) {
            $token = new OAuth1Token($ssoIdentity);
            $credentials->setOAuth1Token($token);
        }

        // see https://tools.ietf.org/html/rfc5849#section-2.3
        $this->addTokenData(
            $token,
            $response->getOAuthToken(),
            [
                'accessToken' => 'token',
                'tokenSecret' => 'tokenSecret',
            ]
        );
    }

    /**
     * @param SsoIdentityInterface|SsoIdentity $ssoIdentity
     * @param UserResponseInterface $response
     */
    protected function applyOAuth2Credentials(SsoIdentityInterface $ssoIdentity, UserResponseInterface $response)
    {
        /** @var SsoIdentity\Credentials $credentials */
        $credentials = $ssoIdentity->getCredentials();

        /** @var OAuth2TokenInterface $token */
        $token = $credentials->getOAuth2Token();
        if (!$token) {
            $token = new OAuth2Token($ssoIdentity);
            $credentials->setOAuth2Token($token);
        }

        if (empty($token->getScope())) {
            $token->setScope($response->getResourceOwner()->getOption('scope'));
        }

        if (empty($token->getTokenType())) {
            $token->setTokenType($response->getOAuthToken()->getRawToken()['token_type'] ?? null);
        }

        // see https://tools.ietf.org/html/rfc6749#section-5.1
        $this->addTokenData(
            $token,
            $response->getOAuthToken(),
            [
                'accessToken' => 'accessToken',
                'refreshToken' => 'refreshToken',
                'expiresAt' => 'expiresAt',
            ]
        );
    }

    /**
     * Add token data from response token to token object/objectbrick. Optionally encrypts value if it is defined as secure
     *
     * @param OAuthTokenInterface $token
     * @param OAuthToken $responseToken
     * @param array $mapping
     */
    protected function addTokenData(OAuthTokenInterface $token, OAuthToken $responseToken, array $mapping)
    {
        foreach ($mapping as $fromProperty => $toProperty) {
            $getter = 'get' . ucfirst($fromProperty);
            $setter = 'set' . ucfirst($toProperty);

            if (!method_exists($responseToken, $getter)) {
                throw new \RuntimeException(sprintf(
                    'Can\'t read property "%s" from token as method "%s" does not exist.',
                    $fromProperty,
                    $getter
                ));
            }

            if (!method_exists($token, $setter)) {
                throw new \RuntimeException(sprintf(
                    'Can\'t apply property "%s" on token as method "%s" does not exist.',
                    $toProperty,
                    $setter
                ));
            }

            $secure = false;
            if (in_array($toProperty, $token->getSecureProperties())) {
                $secure = true;
            }

            $value = $responseToken->$getter();
            if ($secure) {
                $value = $this->encryptionService->encrypt($value);
            }

            $token->$setter($value);
        }
    }

    protected function applyProfileToCustomer(CustomerInterface $customer, UserResponseInterface $response)
    {
        $properties = [
            'email' => $response->getEmail(),
            'firstname' => $response->getFirstName(),
            'lastname' => $response->getLastName(),
        ];

        foreach ($properties as $property => $value) {
            $this->setIfEmpty($customer, $property, $value);
        }

        $responseProperties = [
            'gender',
            'street',
            'zip',
            'city',
            'countryCode',
            'phone'
        ];

        $rawResponse = $response->getData();
        foreach ($responseProperties as $responseProperty) {
            if (isset($rawResponse[$responseProperty])) {
                $this->setIfEmpty($customer, $responseProperty, $rawResponse[$responseProperty]);
            }
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param string $property
     * @param mixed $value
     *
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
}
