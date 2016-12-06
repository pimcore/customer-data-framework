<?php

namespace CustomerManagementFramework\Model;

interface SsoAwareCustomerInterface
{
    /**
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities();

    /**
     * @param SsoIdentityInterface[] $ssoIdentities
     */
    public function setSsoIdentities($ssoIdentities);
}
