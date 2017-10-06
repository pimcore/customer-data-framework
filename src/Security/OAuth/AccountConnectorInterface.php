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

use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Pimcore\Model\DataObject\SsoIdentity;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccountConnectorInterface extends \HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface
{
    /**
     * Connects a user to a oAuth response and returns the generated SsoIdentity
     *
     * @param UserInterface $user
     * @param UserResponseInterface $response
     *
     * @return SsoIdentityInterface|SsoIdentity
     */
    public function connectToSsoIdentity(UserInterface $user, UserResponseInterface $response): SsoIdentityInterface;
}
