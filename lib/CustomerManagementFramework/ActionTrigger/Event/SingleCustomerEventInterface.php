<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:32
 */

namespace CustomerManagementFramework\ActionTrigger\Event;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFramework\Model\CustomerInterface;

interface SingleCustomerEventInterface extends EventInterface
{

    public function __construct(CustomerInterface $customer);

    public function getCustomer();
}