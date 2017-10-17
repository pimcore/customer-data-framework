<?php

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
