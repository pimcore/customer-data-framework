<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model\OAuth;

/**
 * OAuth1 token response model
 *
 * @see https://tools.ietf.org/html/rfc5849#section-2.3
 */
interface OAuth1TokenInterface extends OAuthTokenInterface
{
    /**
     * Get token
     *
     * @return string
     */
    public function getToken();

    /**
     * Set token
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token);

    /**
     * Get token secret
     *
     * @return string
     */
    public function getTokenSecret();

    /**
     * Set token secret
     *
     * @param string $tokenSecret
     *
     * @return $this
     */
    public function setTokenSecret($tokenSecret);
}
