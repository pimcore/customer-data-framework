<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Security\UserProvider;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerObjectUserProvider implements UserProviderInterface
{
    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var string
     */
    protected $usernameField = 'email';

    public function __construct(
        CustomerProviderInterface $customerProvider,
        string $usernameField = 'email'
    ) {
        $this->customerProvider = $customerProvider;
        $this->usernameField = $usernameField;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $list = $this->customerProvider->getList();
        $list->setCondition(sprintf('%s = ?', $this->usernameField), $identifier);
        $this->customerProvider->addActiveCondition($list);

        /** @var CustomerInterface|false $customer */
        $customer = $list->current();

        if (!$customer instanceof UserInterface) {
            throw new UserNotFoundException(sprintf('Customer "%s" was not found', $identifier));
        }

        return $customer;
    }

    /**
     * @deprecated use loadUserByIdentifier() instead.
     *
     *
     */
    public function loadUserByUsername($username)
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     *
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        $class = $this->customerProvider->getCustomerClassName();
        if (!$user instanceof $class || !$user instanceof AbstractObject) {
            throw new UnsupportedUserException();
        }

        $customer = $this->customerProvider->getById($user->getId(), true);

        if (!$customer instanceof UserInterface) {
            throw new UserNotFoundException(sprintf('Customer "%s" was not found', $user->getId()));
        }

        return $customer;
    }

    /**
     *
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === $this->customerProvider->getCustomerClassName();
    }
}
