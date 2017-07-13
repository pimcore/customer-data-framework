<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 11/07/2017
 * Time: 15:28
 */

namespace CustomerManagementFrameworkBundle\Authentication\UserProvider;


use CustomerManagementFrameworkBundle\Authentication\User\CustomerObjectUser;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Object\AbstractObject;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomerObjectUserProvider implements UserProviderInterface
{
    /**
     * @var string
     */
    protected $usernameField = 'email';

    /**
     * @param string $className
     * @param string $usernameField
     */
    public function __construct($usernameField = 'email')
    {
        $this->usernameField = $usernameField;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $list = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();
        $list->setCondition(sprintf('active = 1 and %s=?', $this->usernameField), $username);

        if (!$customer = $list->current()) {
            throw new UsernameNotFoundException(sprintf("user with username %s not found", $username));
        }

        return $customer;
    }

    /**
     * @param UserInterface $user
     * @return mixed
     */
    public function refreshUser(UserInterface $user)
    {
        $class = \Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassName();
        if (!$user instanceof $class || !$user instanceof AbstractObject) {
            throw new UnsupportedUserException();
        }

        return \Pimcore::getContainer()->get('cmf.customer_provider')->getById($user->getId(), true);
    }

    public function supportsClass($class)
    {
        if ($class === \Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassName()) {
            return true;
        }

        if ($class === \Pimcore::getContainer()->get('cmf.customer_provider')->getDiClassName()) {
            return true;
        }

        return false;
    }

}