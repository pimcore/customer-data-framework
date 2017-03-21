<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:33
 */

namespace CustomerManagementFramework\ActionTrigger\Event;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Model\ActivityInterface;

class AfterTrackActivity extends AbstractSingleCustomerEvent{

    /**
     * @var ActivityInterface $activity
     */
    private $activity;

    /** @var ActivityStoreEntryInterface */
    private $entry;

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

    /**
     * @return ActivityStoreEntryInterface
     */
    public function getEntry() {
        return $this->entry;
    }

    /**
     * @param ActivityStoreEntryInterface $entry
     */
    public function setEntry( $entry ) {
        $this->entry = $entry;
    }



    public function getName(){
        return "plugin.cmf.after-track-activity";
    }


}