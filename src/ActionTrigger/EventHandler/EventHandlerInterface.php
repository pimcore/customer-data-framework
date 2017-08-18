<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\EventHandler;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\CustomerListEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;

interface EventHandlerInterface
{
    public function handleEvent($event);

    public function handleSingleCustomerEvent(SingleCustomerEventInterface $event);

    public function handleCustomerListEvent(CustomerListEventInterface $event);
}
