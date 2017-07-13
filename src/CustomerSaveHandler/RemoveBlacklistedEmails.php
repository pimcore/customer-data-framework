<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:35
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