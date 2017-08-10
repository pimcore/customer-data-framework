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
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Security\OAuth;

use CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\User\AbstractUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Utility class supporting registration process from OAuth responses.
 */
class OAuthRegistrationHandler
{
    /**
     * @var OAuthUtils
     */
    private $oAuthUtils;

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
        SsoIdentityServiceInterface $ssoIdentityService,
        AccountConnectorInterface $accountConnector
    )
    {
        $this->oAuthUtils         = $oAuthUtils;
        $this->ssoIdentityService = $ssoIdentityService;
        $this->accountConnector   = $accountConnector;
    }

    /**
     * Saves an OAuth token to the session with a given key. Will be used to memorize the token during registration
     * (multiple requests when handling forms).
     *
     * @param Request $request
     * @param string $key
     * @param OAuthToken $token
     */
    public function saveOAuthTokenToSession(Request $request, string $key, OAuthToken $token)
    {
        $session = $request->getSession();

        $session->set($this->buildSessionKey('token', $key), $token);
        $session->set($this->buildSessionKey('timestamp', $key), time());
    }

    /**
     * Loads a previously stored OAuth token from the session
     *
     * @param Request $request
     * @param string $key
     * @param int $maxLifetime
     *
     * @return OAuthToken|null
     */
    public function loadOAuthTokenFromSession(Request $request, string $key, int $maxLifetime = 300)
    {
        $session = $request->getSession();

        $timestamp = $this->getAndRemoveValueFromSession($session, $this->buildSessionKey('timestamp', $key));
        $token     = $this->getAndRemoveValueFromSession($session, $this->buildSessionKey('token', $key));

        if (null !== $timestamp && (time() - $timestamp) <= $maxLifetime) {
            return $token;
        }
    }

    /**
     * If an OAuth error is stored in the session for the given key, load user information from the resource
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
        $ssoIdentity->save();

        $user->save();

        return $ssoIdentity;
    }

    private function getAndRemoveValueFromSession(SessionInterface $session, string $name, $default = null)
    {
        $value = $session->get($name, $default);
        $session->remove($name);

        return $value;
    }

    private function buildSessionKey(string $type, string $key): string
    {
        return sprintf('cmf.oauth.registration.%s.%s', $type, $key);
    }
}
