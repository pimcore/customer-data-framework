<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class AbstractSingleCustomerEvent extends GenericEvent implements SingleCustomerEventInterface
{
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
        parent::__construct();
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
