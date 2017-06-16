<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:32
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface SingleCustomerEventInterface extends EventInterface
{

    public function __construct(CustomerInterface $customer);

    public function getCustomer();
}