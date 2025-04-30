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

namespace CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class DuplicateCustomerException extends \Pimcore\Model\Element\ValidationException
{
    /**
     * @var CustomerInterface
     */
    private $duplicateCustomer;

    /**
     * @var array
     */
    private $matchedDuplicateFields;

    /**
     * @return CustomerInterface
     */
    public function getDuplicateCustomer()
    {
        return $this->duplicateCustomer;
    }

    /**
     * @param CustomerInterface $duplicateCustomer
     */
    public function setDuplicateCustomer($duplicateCustomer)
    {
        $this->duplicateCustomer = $duplicateCustomer;
    }

    /**
     * returns the field combination where the duplicate was found
     *
     * @return array
     */
    public function getMatchedDuplicateFields()
    {
        return $this->matchedDuplicateFields;
    }

    /**
     * @param array $matchedDuplicateFields
     */
    public function setMatchedDuplicateFields($matchedDuplicateFields)
    {
        $this->matchedDuplicateFields = $matchedDuplicateFields;
    }
}
