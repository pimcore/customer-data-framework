<?php

namespace CustomerManagementFrameworkBundle\Model\Objectbrick;

use CustomerManagementFrameworkBundle\Model\OAuth\OAuth2TokenInterface;
use Pimcore\Model\Object\Objectbrick\Data\AbstractData;

abstract class AbstractOAuth2Token extends AbstractData implements OAuth2TokenInterface
{
    /**
     * @return array
     */
    public function getSecureProperties()
    {
        return [
            'accessToken',
            'refreshToken',
        ];
    }
}
