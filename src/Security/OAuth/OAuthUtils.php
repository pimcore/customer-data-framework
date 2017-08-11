<?php

declare(strict_types=1);

namespace CustomerManagementFrameworkBundle\Security\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

class OAuthUtils extends \HWI\Bundle\OAuthBundle\Security\OAuthUtils
{
    /**
     * @param string $name
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner($name)
    {
        return parent::getResourceOwner($name);
    }
}
