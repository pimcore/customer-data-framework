<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 11/07/2017
 * Time: 15:28
 */

namespace CustomerManagementFrameworkBundle\Authentication\UserProvider;


use CustomerManagementFrameworkBundle\Authentication\User\CustomerObjectUser;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Object\AbstractObject;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerObjectUserProvider implements UserProviderInterface
{
    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var string
     */
    protected $usernameField = 'email';

    /**
     * @param CustomerProviderInterface $customerProvider
     * @param string $usernameField
     */
    public function __construct(CustomerProviderInterface $customerProvider, $usernameField = 'email')
    {
        $this->customerProvider = $customerProvider;
        $this->usernameField = $usernameField;
    }

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($username)
    {
        $list = $this->customerProvider->getList();
        $list->setCondition(sprintf('active = 1 and %s = ?', $this->usernameField), $username);

        if (!$customer = $list->current()) {
            throw new UsernameNotFoundException(sprintf("user with username %s not found", $username));
        }

        return $customer;
    }

    /**
     * @inheritdoc
     */
    public function refreshUser(UserInterface $user)
    {
        $class = $this->customerProvider->getCustomerClassName();
        if (!$user instanceof $class || !$user instanceof AbstractObject) {
            throw new UnsupportedUserException();
        }

        return $this->customerProvider->getById($user->getId(), true);
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        if ($class === $this->customerProvider->getCustomerClassName()) {
            return true;
        }

        return false;
    }
}
