<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
