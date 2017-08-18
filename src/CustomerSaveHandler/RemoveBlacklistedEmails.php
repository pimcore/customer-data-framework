<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
