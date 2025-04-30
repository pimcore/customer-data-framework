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

namespace CustomerManagementFrameworkBundle\Model\Traits;

use Pimcore\Model\DataObject\ClassDefinition\Data\Password;

trait DefaultUserawareCustomerTrait
{
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * By default email is used as username
     *
     */
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /**
     * By default email is used as username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getUserIdentifier();
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
