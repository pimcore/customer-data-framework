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

namespace CustomerManagementFrameworkBundle\Security\Authentication;

use Pimcore\Http\RequestHelper;
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

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        RequestHelper $requestHelper,
        FirewallMap $firewallMap,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        TokenStorageInterface $tokenStorage,
        UserCheckerInterface $defaultUserChecker,
        ContainerInterface $container
    ) {
        $this->firewallMap = $firewallMap;
        $this->requestHelper = $requestHelper;
        $this->sessionStrategy = $sessionStrategy;
        $this->tokenStorage = $tokenStorage;
        $this->defaultUserChecker = $defaultUserChecker;
        $this->container = $container;
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
        return new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
    }

    private function getUserChecker(FirewallConfig $config): UserCheckerInterface
    {
        if ($this->container->has($config->getUserChecker())) {
            /** @var UserCheckerInterface $userChecker */
            $userChecker = $this->container->get($config->getUserChecker());

            return $userChecker;
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

        foreach ($definitions as $definition) {
            if ($this->container->has($definition)) {
                /** @var RememberMeServicesInterface $rememberMeService */
                $rememberMeService = $this->container->get($definition);

                return $rememberMeService;
            }
        }
    }
}
