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

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use CustomerManagementFrameworkBundle\Security\SsoIdentity\SsoIdentityServiceInterface;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Pimcore\Model\User\AbstractUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Utility class supporting registration process from OAuth responses. This is mostly a facade piping
 * requests to other services.
 */
class OAuthRegistrationHandler
{
    /**
     * @var OAuthUtils
     */
    private $oAuthUtils;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var SsoIdentityServiceInterface
     */
    private $ssoIdentityService;

    /**
     * @var AccountConnectorInterface
     */
    private $accountConnector;

    public function __construct(
        OAuthUtils $oAuthUtils,
        TokenStorageInterface $tokenStorage,
        SsoIdentityServiceInterface $ssoIdentityService,
        AccountConnectorInterface $accountConnector
    ) {
        $this->oAuthUtils = $oAuthUtils;
        $this->tokenStorage = $tokenStorage;
        $this->ssoIdentityService = $ssoIdentityService;
        $this->accountConnector = $accountConnector;
    }

    /**
     * Saves an OAuth token (e.g. to the session) with a given key. Will be used to memorize the token during registration
     * (multiple requests when handling forms).
     *
     * @param string $key
     * @param OAuthToken $token
     */
    public function saveToken(string $key, OAuthToken $token)
    {
        $this->tokenStorage->saveToken($key, $token);
    }

    /**
     * Loads a previously stored OAuth token
     *
     * @param string $key
     * @param int $maxLifetime
     *
     * @return OAuthToken|null
     */
    public function loadToken(string $key, int $maxLifetime = 300)
    {
        return $this->tokenStorage->loadToken($key, $maxLifetime);
    }

    /**
     * If an OAuth token is stored for the given key, load user information from the resource
     * owner (provider).
     *
     * @param OAuthToken $token
     * @param array $extraParameters
     *
     * @return UserResponseInterface|null
     */
    public function loadUserInformation(OAuthToken $token, array $extraParameters = [])
    {
        $resourceOwner = $this->oAuthUtils->getResourceOwner($token->getResourceOwnerName());

        return $resourceOwner->getUserInformation($token->getRawToken(), $extraParameters);
    }

    /**
     * Loads customer from OAuth response
     *
     * @param UserResponseInterface $response
     *
     * @return CustomerInterface|null
     */
    public function getCustomerFromUserResponse(UserResponseInterface $response)
    {
        return $this->ssoIdentityService->getCustomerBySsoIdentity(
            $response->getResourceOwner()->getName(),
            $response->getUsername()
        );
    }

    /**
     * Adds OAuth response data to customer object and creates SsoIdentity
     *
     * @param CustomerInterface|UserInterface|AbstractUser $user
     * @param UserResponseInterface $userInformation
     *
     * @return \CustomerManagementFrameworkBundle\Model\SsoIdentityInterface
     */
    public function connectSsoIdentity(UserInterface $user, UserResponseInterface $userInformation): SsoIdentityInterface
    {
        if (!$user->getId()) {
            throw new \LogicException('Can\'t add a SSO identity to a customer which is not saved. Please save the customer first');
        }

        $ssoIdentity = $this->accountConnector->connectToSsoIdentity($user, $userInformation);

        // the connector does not save the customer and the identity
        $ssoIdentity->save();
        $user->save();

        return $ssoIdentity;
    }

    /**
     * Loads a resource owner by name
     *
     * @param string $name
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner(string $name): ResourceOwnerInterface
    {
        return $this->oAuthUtils->getResourceOwner($name);
    }

    /**
     * Builds authorization URL for a given resource owner
     *
     * @param Request $request
     * @param string $resourceOwner
     * @param string $redirectUrl
     * @param array $extraParameters
     *
     * @return string
     */
    public function getAuthorizationUrl(Request $request, string $resourceOwner, string $redirectUrl, array $extraParameters = [])
    {
        return $this->oAuthUtils->getAuthorizationUrl($request, $resourceOwner, $redirectUrl, $extraParameters);
    }
}
