<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Security\UserProvider;

use CustomerManagementFrameworkBundle\Security\OAuth\Exception\AccountNotLinkedException;
use CustomerManagementFrameworkBundle\Security\SsoIdentity\SsoIdentityServiceInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Decorates a user provider and adds OAUth provider capabilities
 */
class OAuthAwareUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var SsoIdentityServiceInterface
     */
    private $ssoIdentityService;

    public function __construct(
        UserProviderInterface $userProvider,
        SsoIdentityServiceInterface $ssoIdentityService
    ) {
        $this->userProvider       = $userProvider;
        $this->ssoIdentityService = $ssoIdentityService;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $provider = $response->getResourceOwner()->getName();
        $username = $response->getUsername();

        $user = $this->ssoIdentityService->getCustomerBySsoIdentity(
            $provider,
            $username
        );

        if (null === $user || null === $username) {
            // the AccountNotLinkedException will allow the frontend to proceed to registration
            // and to fetch user data from the OAuth account
            $exception = new AccountNotLinkedException(sprintf(
                'No customer was found for user "%s" on provider "%s"',
                $username,
                $provider
            ));

            $exception->setUsername($username);

            throw $exception;
        }

        return $user;
    }

    public function loadUserByUsername($username)
    {
        return $this->userProvider->loadUserByUsername($username);
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->userProvider->refreshUser($user);
    }

    public function supportsClass($class)
    {
        return $this->userProvider->supportsClass($class);
    }
}
