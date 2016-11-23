<?php

namespace CustomerManagementFramework\Model;

interface SsoIdentityInterface
{
    /**
     * @return string
     */
    public function getProvider();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return mixed
     */
    public function getProfileData();
}
