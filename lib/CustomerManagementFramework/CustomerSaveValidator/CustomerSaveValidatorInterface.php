<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 21.11.2016
 * Time: 13:51
 */

namespace CustomerManagementFramework\CustomerSaveValidator;

use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Model\Element\ValidationException;

interface CustomerSaveValidatorInterface {

    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     * @throws ValidationException
     */
    public function validate(CustomerInterface $customer);
}