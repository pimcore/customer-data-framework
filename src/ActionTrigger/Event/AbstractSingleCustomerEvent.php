<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractSingleCustomerEvent extends Event implements SingleCustomerEventInterface
{
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        return false;
    }
}
