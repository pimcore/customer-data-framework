<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Security\SsoIdentity;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;

interface SsoIdentityServiceInterface
{
    /**
     * @param CustomerInterface $customer
     *
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(CustomerInterface $customer);

    /**
     * @param string $provider
     * @param string $identifier
     *
     * @return CustomerInterface|null
     */
    public function getCustomerBySsoIdentity($provider, $identifier);

    /**
     * @param CustomerInterface $customer
     * @param string $provider
     * @param string $identifier
     *
     * @return SsoIdentityInterface|null
     */
    public function getSsoIdentity(CustomerInterface $customer, $provider, $identifier);

    /**
     * @param CustomerInterface $customer
     * @param SsoIdentityInterface $ssoIdentity
     *
     * @return $this
     */
    public function addSsoIdentity(CustomerInterface $customer, SsoIdentityInterface $ssoIdentity);

    /**
     * @param CustomerInterface $customer
     * @param string $provider
     * @param string $identifier
     * @param mixed $profileData
     *
     * @return SsoIdentityInterface
     */
    public function createSsoIdentity(CustomerInterface $customer, $provider, $identifier, $profileData);
}
