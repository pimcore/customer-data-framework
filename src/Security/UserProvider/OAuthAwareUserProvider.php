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
        $this->userProvider = $userProvider;
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
