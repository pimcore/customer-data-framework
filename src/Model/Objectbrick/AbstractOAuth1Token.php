<?php

namespace CustomerManagementFrameworkBundle\Model\Objectbrick;

use CustomerManagementFrameworkBundle\Model\OAuth\OAuth1TokenInterface;
use Pimcore\Model\Object\Objectbrick\Data\AbstractData;

abstract class AbstractOAuth1Token extends AbstractData implements OAuth1TokenInterface
{
    /**
     * @return array
     */
    public function getSecureProperties()
    {
        return [
            'token',
            'tokenSecret'
        ];
    }
}
