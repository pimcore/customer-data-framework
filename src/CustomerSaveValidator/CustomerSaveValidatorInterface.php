<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveValidator;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Element\ValidationException;

/**
 * Interface CustomerSaveValidatorInterface
 *
 * checks if a customer is allowed to save based on the entered customer data
 *
 * @package CustomerManagementFramework\CustomerSaveValidator
 */
interface CustomerSaveValidatorInterface
{
    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function validate(CustomerInterface $customer, $withDuplicatesCheck = true);
}
