<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
     * @param CustomerInterface $customer
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
