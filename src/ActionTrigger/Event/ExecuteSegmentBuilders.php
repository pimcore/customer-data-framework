<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:33
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;

class ExecuteSegmentBuilders extends AbstractSingleCustomerEvent{


    public function getName(){
        return "plugin.cmf.execute-segment-builders";
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if($trigger->getEventName() != $this->getName()) {
            return false;
        }

        return true;
    }


}