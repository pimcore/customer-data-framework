<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-06
 * Time: 15:22
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class DuplicateCustomerException extends \Pimcore\Model\Element\ValidationException {

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