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

namespace CustomerManagementFrameworkBundle\Security\Authenticator;

use Pimcore\Model\Tool\SettingsStore;
use Pimcore\Model\User;
use Pimcore\Security\User\User as UserProxy;
use Pimcore\Tool\Authentication;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\InteractiveAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PreAuthenticatedUserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class WebserviceAuthenticator extends AbstractAuthenticator implements InteractiveAuthenticatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const SETTINGS_STORE_KEY = 'api_keys';
    const SETTINGS_STORE_SCOPE = 'cmf';

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): ?bool
    {
        return true;
    }

    /**
     * Get the authentication credentials from the request as any an associate array.
     * Check credentials in the passport through CustomCredentials
     *
     * @param Request $request
     *
     * @return Passport
     */
    public function authenticate(Request $request): Passport
    {
        if ($apiKey = $request->headers->get('x_api-key') ?? $request->get('apikey')) {
            $credentials['apiKey'] = $apiKey;
        } elseif (null !== $pimcoreUser = Authentication::authenticateSession()) { // check for existing session user
            $credentials['user'] = $pimcoreUser;
        } else {
            throw $this->createAccessDeniedException();
        }

        $user = null;
        if (isset($credentials['user']) && $credentials['user'] instanceof User) {
            $user = new UserProxy($credentials['user']);
        } elseif (isset($credentials['apiKey'])) {
            $pimcoreUser = $this->loadUserForApiKey($credentials['apiKey']);
            if ($pimcoreUser instanceof User) {
                $user = new UserProxy($pimcoreUser);
            }
        }

        if (!$user || !Authentication::isValidUser($user->getUser())) {
            throw new AuthenticationException('Invalid credentials.');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier()),
            [new PreAuthenticatedUserBadge()]
        );
    }

    private function createAccessDeniedException(\Throwable $previous = null)
    {
        return new AccessDeniedHttpException('API request needs either a valid API key or a valid session.', $previous);
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
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->warning('Failed to authenticate for webservice request {path}', [
            'path' => $request->getPathInfo(),
        ]);

        throw $this->createAccessDeniedException($exception);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->debug('Successfully authenticated user {user} for webservice request {path}', [
            'user' => $token->getUser()->getUserIdentifier(),
            'path' => $request->getPathInfo(),
        ]);

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isInteractive(): bool
    {
        return true;
    }
}
