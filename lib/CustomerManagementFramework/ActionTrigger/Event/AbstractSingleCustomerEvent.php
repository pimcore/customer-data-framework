<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:33
 */

namespace CustomerManagementFramework\ActionTrigger\Event;

use CustomerManagementFramework\Model\CustomerInterface;

abstract class AbstractSingleCustomerEvent implements SingleCustomerEventInterface {

    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

}