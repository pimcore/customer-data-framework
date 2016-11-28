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

class NewActivity extends AbstractEvent{

    /**
     * @var ActivityInterface $activity
     */
    private $activity;

    const OPTION_TYPE = 'type';

    /**
     * @return ActivityInterface
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * @param ActivityInterface $activity
     */
    public function setActivity(ActivityInterface $activity)
    {
        $this->activity = $activity;
    }

    public function getName(){
        return "plugin.cmf.new-activity";
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if($trigger->getEventName() != $this->getName()) {
            return false;
        }

        $options = $trigger->getOptions();
        if(!empty($options[self::OPTION_TYPE])) {
            if($this->activity->cmfGetType() != $options[self::OPTION_TYPE]) {
                return false;
            }
        }

        return true;
    }


}