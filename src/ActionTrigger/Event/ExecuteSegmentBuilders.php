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

class ExecuteSegmentBuilders extends AbstractSingleCustomerEvent
{
    public function getName()
    {
        return 'plugin.cmf.execute-segment-builders';
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if ($trigger->getEventName() != $this->getName()) {
            return false;
        }

        return true;
    }
}
