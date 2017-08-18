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

interface EventInterface
{
    public function getName();

    public function appliesToTrigger(TriggerDefinitionInterface $trigger);
}
