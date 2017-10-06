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
