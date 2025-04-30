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

namespace CustomerManagementFrameworkBundle\CustomerSaveHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

/**
 * removes email address from customer if it is blacklisted
 *
 * @package CustomerManagementFramework\CustomerSaveHandler
 */
class RemoveBlacklistedEmails extends AbstractCustomerSaveHandler
{
    /**
     *
     * @return void
     */
    public function preSave(CustomerInterface $customer)
    {
        if ($this->isBlacklisted($customer->getEmail())) {
            $customer->setEmail(null);
        }
    }

    private function isBlacklisted($email)
    {
        $email = strtolower(trim($email));

        $validator = new \CustomerManagementFrameworkBundle\DataValidator\BlacklistValidator();

        return !$validator->isValid($email);
    }
}
