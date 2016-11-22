<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:48
 */

namespace CustomerManagementFramework\ActionTrigger\Queue;

use CustomerManagementFramework\ActionTrigger\Rule;
use CustomerManagementFramework\Model\CustomerInterface;

interface QueueInterface {

    public function addToEventQueue(CustomerInterface $customer, Rule $rule, $actionDateTimestamp);
}