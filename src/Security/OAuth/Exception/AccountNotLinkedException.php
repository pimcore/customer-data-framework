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

namespace CustomerManagementFrameworkBundle\Security\OAuth\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class AccountNotLinkedException extends \HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException
{
    public function getToken(): OAuthToken
    {
        return $this->token;
    }
}
