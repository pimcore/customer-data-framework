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

use CustomerManagementFrameworkBundle\CustomerSaveValidator\Exception\DuplicateCustomerException;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\Element\ValidationException;

class DefaultCustomerSaveValidator implements CustomerSaveValidatorInterface
{
    /**
     * @var array
     */
    private $requiredFields;

    /**
     * @var bool
     */
    private $checkForDuplicates;

    /**
     * DefaultCustomerSaveValidator constructor.
     *
     * @param array $requiredFields
     * @param bool $checkForDuplicates
     */
    public function __construct(array $requiredFields, $checkForDuplicates)
    {
        $this->requiredFields = $requiredFields;
        $this->checkForDuplicates = $checkForDuplicates;
    }

    /**
     * @param CustomerInterface $customer
     * @param bool $withDuplicatesCheck
     *
     * @return bool
     *
     * @throws DuplicateCustomerException
     * @throws ValidationException
     */
    public function validate(CustomerInterface $customer, $withDuplicatesCheck = true)
    {
        $validRequiredFields = $this->validateRequiredFields($customer);

        $validDuplicates = true;
        if ($withDuplicatesCheck) {
            $validDuplicates = $this->validateDuplicates($customer);
        }

        return $validRequiredFields && $validDuplicates;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     *
     * @throws ValidationException
     */
    protected function validateRequiredFields(CustomerInterface $customer)
    {
        if (!sizeof($this->requiredFields)) {
            return true;
        }

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

    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     *
     * @throws DuplicateCustomerException
     */
    protected function validateDuplicates(CustomerInterface $customer)
    {
        if ($this->checkForDuplicates && $customer->getActive() && $customer->getPublished()) {
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

    /**
     * @param CustomerInterface $customer
     * @param string $field
     * @param bool $throwException
     *
     * @return bool
     *
     * @throws ValidationException
     */
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
