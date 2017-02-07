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

    public function __construct(LoggerInterface $logger);

    public function mergeCustomers(CustomerInterface $sourceCustomer, CustomerInterface $destinationCustomer);
}