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

/**
 * Interface CustomerSaveValidatorInterface
 *
 * checks if a customer is allowed to save based on the entered customer data
 *
 * @package CustomerManagementFramework\CustomerSaveValidator
 */
interface CustomerSaveValidatorInterface {

    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     * @throws ValidationException
     */
    public function validate(CustomerInterface $customer);
}