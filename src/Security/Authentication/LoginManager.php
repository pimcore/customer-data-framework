<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Security\Authentication;

use CustomerManagementFrameworkBundle\Model\ClassDefinition\Helper\RememberMeServiceResolver;
use CustomerManagementFrameworkBundle\Model\ClassDefinition\Helper\UserCheckerClassResolver;
use Pimcore\Http\RequestHelper;
use Pimcore\Logger;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * Handles manual user logins (e.g. from registration). The logic implemented here basically
 * resembles to LoginManager in FOSUserBundle, but resolves firewall config, user checkers and
 * remember me services dynamically from the firewall config instead of wiring it toghether via
 * compiler passes as we don't have a config defining which firewall to use in CMF.
 */
class LoginManager implements LoginManagerInterface
{
    /**
     * @var RequestHelper
     */
    private $requestHelper;

    /**
     * @var FirewallMap
     */
    private $firewallMap;

    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserCheckerInterface
     */
    private $defaultUserChecker;


    public function __construct(
        RequestHelper $requestHelper,
        FirewallMap $firewallMap,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $defaultUserChecker
    ) {
        $this->firewallMap = $firewallMap;
        $this->requestHelper = $requestHelper;
        $this->sessionStrategy = $sessionStrategy;
        $this->tokenStorage = $tokenStorage;
        $this->defaultUserChecker = $defaultUserChecker;
    }

    /**
     * @inheritdoc
     */
    public function login(UserInterface $user, Request $request = null, Response $response = null)
    {
        if (null === $request) {
            $request = $this->requestHelper->getCurrentRequest();
        }

        $firewallConfig = $this->firewallMap->getFirewallConfig($request);
        $userChecker = $this->getUserChecker($firewallConfig);
        $rememberMeService = $this->getRememberMeService($firewallConfig);

        $userChecker->checkPreAuth($user);

        $token = $this->createToken($firewallConfig->getName(), $user);

        $this->sessionStrategy->onAuthentication($request, $token);

        if (null !== $response && null !== $rememberMeService) {
            $rememberMeService->loginSuccess($request, $response, $token);
        }

        $this->tokenStorage->setToken($token);
    }

    private function createToken(string $firewallName, UserInterface $user): TokenInterface
    {
        return new UsernamePasswordToken($user, $firewallName, $user->getRoles());
    }

    private function getUserChecker(FirewallConfig $config): UserCheckerInterface
    {
        try {
            return UserCheckerClassResolver::resolveUserChecker($config->getUserChecker());
        } catch (\Throwable $e) {
            Logger::error($e->getMessage());
        }

        return $this->defaultUserChecker;
    }

    /**
     * @param FirewallConfig $config
     *
     * @return RememberMeServicesInterface|null
     */
    private function getRememberMeService(FirewallConfig $config)
    {
        $definitions = [
            'security.authentication.rememberme.services.persistent.' . $config->getName(),
            'security.authentication.rememberme.services.simplehash.' . $config->getName()
        ];

        return RememberMeServiceResolver::resolveRememberMeService($definitions);
    }
}
