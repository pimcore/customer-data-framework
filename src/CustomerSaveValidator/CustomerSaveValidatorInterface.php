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
