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

class AfterTrackActivity extends AbstractSingleCustomerEvent{

    /**
     * @var ActivityInterface $activity
     */
    private $activity;

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
        return "plugin.cmf.after-track-activity";
    }


}