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

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionTokenStorage implements TokenStorageInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Saves an OAuth token with a given key.
     *
     * @param string $key
     * @param OAuthToken $token
     */
    public function saveToken(string $key, OAuthToken $token)
    {
        $this->session->set($this->buildSessionKey('token', $key), $token);
        $this->session->set($this->buildSessionKey('timestamp', $key), time());
    }

    /**
     * Loads a previously stored OAuth token. maxLifetime defines the maximum lifetime a token
     * can have before being discarded.
     *
     * @param string $key
     * @param int $maxLifetime
     *
     * @return OAuthToken|null
     */
    public function loadToken(string $key, int $maxLifetime = 300)
    {
        $timestamp = $this->getAndRemoveValueFromSession($this->buildSessionKey('timestamp', $key));
        $token = $this->getAndRemoveValueFromSession($this->buildSessionKey('token', $key));

        if (null !== $timestamp && (time() - $timestamp) <= $maxLifetime) {
            return $token;
        }
    }

    private function getAndRemoveValueFromSession(string $name, $default = null)
    {
        $value = $this->session->get($name, $default);
        $this->session->remove($name);

        return $value;
    }

    private function buildSessionKey(string $type, string $key): string
    {
        return sprintf('cmf.oauth.token.%s.%s', $type, $key);
    }
}
