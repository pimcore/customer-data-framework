<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 21.11.2016
 * Time: 13:51
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveValidator;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Element\ValidationException;

class DefaultCustomerSaveValidator implements CustomerSaveValidatorInterface
{
    private $config;

    /**
     * @var array
     */
    private $requiredFields;

    public function __construct()
    {
        $config = Config::getConfig();
        $this->config = $config->CustomerSaveValidator;

        $this->requiredFields = $this->config->requiredFields ? $this->config->requiredFields->toArray() : [];
    }

    public function validate(CustomerInterface $customer, $withDuplicatesCheck = true)
    {
        $validRequiredFields = $this->validateRequiredFields($customer);

        $validDuplicates = true;
        if ($withDuplicatesCheck) {
            $validDuplicates = $this->validateDuplicates($customer);
        }

        return $validRequiredFields && $validDuplicates;
    }

    protected function validateRequiredFields(CustomerInterface $customer)
    {
        $valid = false;
        foreach ($this->requiredFields as $requiredFields) {
            if (is_array($requiredFields)) {
                $valid = true;
                foreach ($requiredFields as $requiredField) {
                    if (!$this->validateField($customer, $requiredField)) {
                        $valid = false;
                    }
                }

                if ($valid) {
                    return true;
                }
            } else {
                $this->validateField($customer, $requiredFields, true);
            }
        }

        if (!$valid) {
            $combinations = [];

            foreach ($this->requiredFields as $requiredFields) {
                $combinations[] = '['.implode(' + ', $requiredFields).']';
            }

            throw new ValidationException(
                'Not all required fields are set. Please fill-up one of the following field combinations: '.implode(
                    ' or ',
                    $combinations
                )
            );
        }

        return true;
    }

    protected function validateDuplicates(CustomerInterface $customer)
    {
        if ($this->config->checkForDuplicates) {
            $duplicates = \Pimcore::getContainer()->get('cmf.customer_duplicates_service')->getDuplicatesOfCustomer(
                $customer
            );
            if (!is_null($duplicates) && $duplicates->getCount()) {
                $ex = new DuplicateCustomerException('Duplicate customer found: ID '.$duplicates->current());

                $ex->setDuplicateCustomer($duplicates->current());
                $ex->setMatchedDuplicateFields(
                    \Pimcore::getContainer()->get('cmf.customer_duplicates_service')->getMatchedDuplicateFields()
                );

                throw $ex;
            }
        }

        return true;
    }

    protected function validateField(CustomerInterface $customer, $field, $throwException = false)
    {
        $getter = 'get'.ucfirst($field);

        $value = $customer->$getter();

        if (is_null($value) || $value === '') {
            if ($throwException) {
                throw new ValidationException(
                    sprintf('Please enter a value for the following required field: %s', $field)
                );
            }

            return false;
        }

        return true;
    }
}
