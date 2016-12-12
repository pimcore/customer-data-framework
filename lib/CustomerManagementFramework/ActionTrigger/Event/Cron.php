<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:33
 */

namespace CustomerManagementFramework\ActionTrigger\Event;

use Cron\CronExpression;
use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;

class Cron implements CustomerListEventInterface
{

    public function getName(){
        return "plugin.cmf.cron-trigger";
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if($trigger->getEventName() != $this->getName()) {
            return false;
        }

        $options = $trigger->getOptions();

        $cron = CronExpression::factory( $options['definition']);

        return $cron->isDue();
    }


}