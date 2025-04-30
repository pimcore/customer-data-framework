<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
