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

namespace CustomerManagementFrameworkBundle\ActionTrigger\EventHandler;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\CustomerListEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;

interface EventHandlerInterface
{
    public function handleEvent($event);

    public function handleSingleCustomerEvent(SingleCustomerEventInterface $event, RuleEnvironmentInterface $environment);

    public function handleCustomerListEvent(CustomerListEventInterface $event, RuleEnvironmentInterface $environment);
}
