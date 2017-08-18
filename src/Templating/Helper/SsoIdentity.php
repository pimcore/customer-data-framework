<?php

declare(strict_types=1);

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
