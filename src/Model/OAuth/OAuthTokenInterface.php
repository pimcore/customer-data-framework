<?php

namespace CustomerManagementFrameworkBundle\Model\OAuth;

interface OAuthTokenInterface
{
    /**
     * @return array
     */
    public function getSecureProperties();
}
