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
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthHandler
{
    /**
     * @var FirewallMap
     */
    private $firewallMap;

    /**
     * @var SsoIdentityServiceInterface
     */
    private $ssoIdentityService;

    /**
     * @var AccountConnectorInterface
     */
    private $accountConnector;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        FirewallMap $firewallMap,
        SsoIdentityServiceInterface $ssoIdentityService,
        AccountConnectorInterface $accountConnector,
        ContainerInterface $container
    )
    {
        $this->firewallMap        = $firewallMap;
        $this->ssoIdentityService = $ssoIdentityService;
        $this->accountConnector   = $accountConnector;
        $this->container          = $container;
    }

    public function saveOAuthErrorToSession(Request $request, string $key, AccountNotLinkedException $error)
    {
        $session = $request->getSession();
        $session->set(sprintf('app.oauth.registration.error.%s', $key), $error);
        $session->set(sprintf('app.oauth.registration.timestamp.%s', $key), time());
    }

    /**
     * @param Request $request
     * @param string $key
     * @param int $maxLifetime
     *
     * @return AccountNotLinkedException|null
     */
    public function loadOAuthErrorFromSession(Request $request, string $key, int $maxLifetime = 300)
    {
        $session = $request->getSession();

        $timestamp = $this->getAndRemoveValueFromSession($session, sprintf('app.oauth.registration.timestamp.%s', $key));
        $error     = $this->getAndRemoveValueFromSession($session, sprintf('app.oauth.registration.error.%s', $key));

        if (null !== $timestamp && (time() - $timestamp) <= $maxLifetime) {
            return $error;
        }
    }

    /**
     * Fetches user information from auth provider
     *
     * @param Request $request
     * @param string $resourceOwner
     * @param array $rawToken
     * @param array $extraParameters
     *
     * @return UserResponseInterface
     */
    public function getUserInformation(Request $request, string $resourceOwner, array $rawToken, array $extraParameters = [])
    {
        $resourceOwner = $this->getResourceOwnerByName($request, $resourceOwner);

        return $resourceOwner->getUserInformation($rawToken, $extraParameters);
    }

    /**
     * Loads customer from OAuth response
     *
     * @param UserResponseInterface $response
     *
     * @return CustomerInterface|null
     */
    public function getCustomerFromAuthResponse(UserResponseInterface $response)
    {
        return $this->ssoIdentityService->getCustomerBySsoIdentity(
            $response->getResourceOwner()->getName(),
            $response->getUsername()
        );
    }

    public function updateUserFromUserInformation(UserInterface $user, UserResponseInterface $userInformation)
    {
        return $this->accountConnector->connectToSsoIdentity($user, $userInformation);
    }

    private function getAndRemoveValueFromSession(SessionInterface $session, string $name, $default = null)
    {
        $value = $session->get($name, $default);
        $session->remove($name);

        return $value;
    }

    /**
     * Get a resource owner by name. Extracted from HWIOAuthBundle ConnectController. This
     * version just loads the resource owner for the current firewall (resolved from the
     * request).
     *
     * @param Request $request
     * @param string $name
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwnerByName(Request $request, string $name)
    {
        $firewallConfig = $this->firewallMap->getFirewallConfig($request);

        $id = 'hwi_oauth.resource_ownermap.' . $firewallConfig->getName();
        if ($this->container->has($id)) {
            $ownerMap = $this->container->get($id);
            if ($resourceOwner = $ownerMap->getResourceOwnerByName($name)) {
                return $resourceOwner;
            }
        }

        throw new \InvalidArgumentException(sprintf('No resource owner with name "%s".', $name));
    }
}
