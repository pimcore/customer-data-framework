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
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Targeting\DataProvider;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Targeting\DataProvider\DataProviderInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Customer implements DataProviderInterface
{
    const PROVIDER_KEY = 'cmf_customer';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @inheritDoc
     */
    public function load(VisitorInfo $visitorInfo)
    {
        if ($visitorInfo->has(self::PROVIDER_KEY)) {
            return;
        }

        $customer = $this->loadCustomer();

        $visitorInfo->set(self::PROVIDER_KEY, $customer);
    }

    private function loadCustomer()
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return null;
        }

        if ($user instanceof CustomerInterface) {
            return $user;
        }
    }
}
