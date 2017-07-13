<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFrameworkBundle\Model\AbstractCustomer;

use CustomerManagementFrameworkBundle\Model\AbstractCustomer;
use Pimcore\Model\Object\ClassDefinition\Data\Password;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class DefaultAbstractUserawareCustomer extends AbstractCustomer implements UserInterface
{
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // user has no salt as we use password_hash
        // which handles the salt by itself
        return null;
    }

    /**
     * By default email is used as username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Trigger the hash calculation to remove the plain text password from the instance. This
     * is necessary to make sure no plain text passwords are serialized.
     *
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        /** @var Password $field */
        $field = $this->getClass()->getFieldDefinition('password');
        $field->getDataForResource($this->getPassword(), $this);
    }


}
