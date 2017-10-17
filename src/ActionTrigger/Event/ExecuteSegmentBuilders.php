<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
