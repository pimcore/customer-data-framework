<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\SsoIdentityInterface;
use CustomerManagementFrameworkBundle\Security\SsoIdentity\SsoIdentityServiceInterface;
use Symfony\Component\Templating\Helper\Helper;

class SsoIdentity extends Helper
{
    /**
     * @var SsoIdentityServiceInterface
     */
    private $ssoIdentityService;

    public function __construct(SsoIdentityServiceInterface $ssoIdentityService)
    {
        $this->ssoIdentityService = $ssoIdentityService;
    }

    public function getName()
    {
        return 'cmfSsoIdentity';
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return SsoIdentityInterface[]
     */
    public function getSsoIdentities(CustomerInterface $customer): array
    {
        return $this->ssoIdentityService->getSsoIdentities($customer);
    }
}
