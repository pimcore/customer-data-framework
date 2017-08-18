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

use Cron\CronExpression;
use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;

class Cron implements CustomerListEventInterface
{
    public function getName()
    {
        return 'plugin.cmf.cron-trigger';
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if ($trigger->getEventName() != $this->getName()) {
            return false;
        }

        $options = $trigger->getOptions();

        $cron = CronExpression::factory($options['definition']);

        return $cron->isDue();
    }
}
