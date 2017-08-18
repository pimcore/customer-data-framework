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

use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Pimcore\Model\Object\SsoIdentity;
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
