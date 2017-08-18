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

namespace CustomerManagementFrameworkBundle\Security\OAuth;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

interface TokenStorageInterface
{
    /**
     * Saves an OAuth token with a given key.
     *
     * @param string $key
     * @param OAuthToken $token
     */
    public function saveToken(string $key, OAuthToken $token);

    /**
     * Loads a previously stored OAuth token. maxLifetime defines the maximum lifetime a token
     * can have before being discarded.
     *
     * @param string $key
     * @param int $maxLifetime
     *
     * @return OAuthToken|null
     */
    public function loadToken(string $key, int $maxLifetime = 300);
}
