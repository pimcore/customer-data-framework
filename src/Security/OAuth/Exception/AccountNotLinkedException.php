<?php

declare(strict_types=1);

namespace CustomerManagementFrameworkBundle\Security\OAuth\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class AccountNotLinkedException extends \HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException
{
    public function getToken(): OAuthToken
    {
        return $this->token;
    }
}
