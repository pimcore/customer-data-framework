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
