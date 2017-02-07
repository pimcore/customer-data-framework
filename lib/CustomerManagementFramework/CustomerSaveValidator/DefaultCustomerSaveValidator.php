<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 21.11.2016
 * Time: 13:51
 */

namespace CustomerManagementFramework\CustomerSaveValidator;

use CustomerManagementFramework\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Element\ValidationException;

class DefaultCustomerSaveValidator implements CustomerSaveValidatorInterface{

    private $config;

    /**
     * @var array
     */
    private $requiredFields;

    public function __construct()
    {
        $config = Plugin::getConfig();
        $this->config = $config->CustomerSaveValidator;

        $this->requiredFields = $this->config->requiredFields ? $this->config->requiredFields->toArray() : [];
    }

    public function validate(CustomerInterface $customer) {

        $validRequiredFields = $this->validateRequiredFields($customer);
        $validDuplicates = $this->validateDuplicates($customer);

        return $validRequiredFields && $validDuplicates;
    }

    protected function validateRequiredFields(CustomerInterface $customer) {

        $valid = false;
        foreach($this->requiredFields as $requiredFields) {
            if(is_array($requiredFields)) {
                $valid = true;
                foreach($requiredFields as $requiredField) {
                    if(!$this->validateField($customer, $requiredField)) {
                        $valid = false;
                    }
                }

                if($valid) {
                    return true;
                }

            } else {
                $this->validateField($customer, $requiredFields, true);
            }
        }

        if(!$valid) {
            $combinations = [];

            foreach($this->requiredFields as $requiredFields) {
                $combinations[] = '[' . implode(' + ', $requiredFields) . ']';
            }

            throw new ValidationException("Not all required fields are set. Please fill-up one of the following field combinations: " . implode(' or ', $combinations));
        }

        return true;
    }

    protected function validateDuplicates(CustomerInterface $customer)
    {
        if($this->config->checkForDuplicates) {

            if($duplicates = Factory::getInstance()->getCustomerDuplicatesService()->getDuplicatesOfCustomer($customer)) {
                $ex = new DuplicateCustomerException("Duplicate customer found: ID " . $duplicates[0]->getId());

                $ex->setDuplicateCustomer($duplicates[0]);
                $ex->setMatchedDuplicateFields(Factory::getInstance()->getCustomerDuplicatesService()->getMatchedDuplicateFields());

                throw $ex;
            }
        }

        return true;
    }

    protected function validateField(CustomerInterface $customer, $field, $throwException = false)
    {
        $getter = 'get' . ucfirst($field);

        if(is_null($customer->$getter())){
            if($throwException) {
                throw new ValidationException(sprintf("Please enter a value for the following required field: %s", $field));
            }

            return false;
        }

        return true;
    }
}