<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
