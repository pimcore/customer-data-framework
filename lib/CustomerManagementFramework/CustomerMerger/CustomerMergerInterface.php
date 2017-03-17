<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-02-07
 * Time: 14:45
 */

namespace CustomerManagementFramework\CustomerMerger;

use CustomerManagementFramework\Model\CustomerInterface;
use Psr\Log\LoggerInterface;

interface CustomerMergerInterface {

    /**
     * Adds all values from source customer to target customer and returns merged target customer instance.
     *
     * @param CustomerInterface $sourceCustomer
     * @param CustomerInterface $targetCustomer
     * @return CustomerInterface
     */
    public function mergeCustomers(CustomerInterface $sourceCustomer, CustomerInterface $targetCustomer);
}