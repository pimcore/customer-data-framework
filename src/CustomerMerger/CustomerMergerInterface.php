<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-07
 * Time: 14:45
 */

namespace CustomerManagementFrameworkBundle\CustomerMerger;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface CustomerMergerInterface {

    /**
     * Adds all values from source customer to target customer and returns merged target customer instance.
     *
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @param bool $mergeAttributes
     * @return CustomerInterface
     */
    public function mergeCustomers(CustomerInterface $sourceCustomer, CustomerInterface $targetCustomer, $mergeAttributes = true);
}