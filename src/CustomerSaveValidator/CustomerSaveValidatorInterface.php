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
     *
     * @return bool
     *
     * @throws ValidationException
     */
    public function validate(CustomerInterface $customer, $withDuplicatesCheck = true);
}
