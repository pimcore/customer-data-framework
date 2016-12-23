<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:33
 */

namespace CustomerManagementFramework\ActionTrigger\Event;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFramework\Model\ActivityInterface;

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