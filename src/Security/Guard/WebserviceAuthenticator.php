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

namespace CustomerManagementFrameworkBundle\Security\Guard;

use Pimcore\Bundle\AdminBundle\Security\User\User as UserProxy;
use Pimcore\Model\Tool\SettingsStore;
use Pimcore\Model\User;
use Pimcore\Tool\Authentication;
use Pimcore\Tool\Session;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class WebserviceAuthenticator extends AbstractGuardAuthenticator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const SETTINGS_STORE_KEY = 'api_keys';
    const SETTINGS_STORE_SCOPE = 'cmf';

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw $this->createAccessDeniedException($authException);
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request)
    {
        if ($apiKey = $request->headers->get('x_api-key')) {
            // check for API key header
            return [
                'apiKey' => $apiKey,
            ];
        } elseif ($apiKey = $request->get('apikey')) {
            // check for API key parameter
            return [
                'apiKey' => $apiKey,
            ];
        } else {
            // check for existing session user
            if (null !== $pimcoreUser = Authentication::authenticateSession()) {
                return [
                    'user' => $pimcoreUser,
                ];
            }
        }

        throw $this->createAccessDeniedException();
    }

    private function createAccessDeniedException(\Throwable $previous = null)
    {
        return new AccessDeniedHttpException('API request needs either a valid API key or a valid session.', $previous);
    }

    /**
     * @inheritDoc
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var UserProxy|null $user */
        $user = null;

        if (!is_array($credentials)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        if (isset($credentials['user']) && $credentials['user'] instanceof User) {
            $user = new UserProxy($credentials['user']);
        } else {
            if (isset($credentials['apiKey'])) {
                $pimcoreUser = $this->loadUserForApiKey($credentials['apiKey']);
                if ($pimcoreUser) {
                    $user = new UserProxy($pimcoreUser);
                }
            }
        }

        if ($user && Authentication::isValidUser($user->getUser())) {
            return $user;
        }

        return null;
    }

    /**
     * @param string $apiKey
     *
     * @return User|null
     *
     * @throws \Exception
     */
    protected function loadUserForApiKey($apiKey)
    {
        $settingsStore = SettingsStore::get(self::SETTINGS_STORE_KEY, self::SETTINGS_STORE_SCOPE);
        $apiKeys = $settingsStore ? json_decode($settingsStore->getData(), true) : [];

        $userId = array_search($apiKey, $apiKeys);
        if ($userId) {
            return User::getById($userId);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // we rely on getUser returning a valid user
        if ($user instanceof UserProxy) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->logger->warning('Failed to authenticate for webservice request {path}', [
            'path' => $request->getPathInfo(),
        ]);

        throw $this->createAccessDeniedException($exception);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $this->logger->debug('Successfully authenticated user {user} for webservice request {path}', [
            'user' => $token->getUser()->getUsername(),
            'path' => $request->getPathInfo(),
        ]);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
