<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 21.11.2016
 * Time: 16:14
 */

namespace CustomerManagementFramework\CustomerDuplicatesService;

use CustomerManagementFramework\Model\CustomerInterface;

interface CustomerDuplicatesServiceInterface {

    public function getDuplicatesOfCustomer(CustomerInterface $customer);
}