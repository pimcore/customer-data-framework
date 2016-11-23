<?php

namespace CustomerManagementFramework\Model;

interface SsoAwareCustomerInterface
{
    /**
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities();

    /**
     * @param $ssoIdentities
     */
    public function setSsoIdentities($ssoIdentities);
}
