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

namespace CustomerManagementFrameworkBundle\Model\AbstractCustomer;

use CustomerManagementFrameworkBundle\Model\AbstractCustomer;
use CustomerManagementFrameworkBundle\Model\Traits;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class DefaultAbstractUserawareCustomer extends AbstractCustomer implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Traits\DefaultUserawareCustomerTrait;
}
