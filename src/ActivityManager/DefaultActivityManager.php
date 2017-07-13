<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFrameworkBundle\ActivityManager;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class DefaultActivityManager implements ActivityManagerInterface
{

    protected $disableEvents = false;

    /**
     * @return bool
     */
    public function isDisableEvents()
    {
        return $this->disableEvents;
    }

    /**
     * Disable dispatching of php events.
     *
     * @param bool $disableEvents
     * @return $this
     */
    public function setDisableEvents( $disableEvents )
    {
        $this->disableEvents = $disableEvents;
        return $this;
    }


    /**
     * Add/update activity in activity store.
     * Each activity is only saved once. The activity will be updated if it already exists in the store.
     *
     * @param ActivityInterface $activity
     * @return void
     *
     * @throws \Exception
     */
    
    public function trackActivity(ActivityInterface $activity  )
    {
        $store = \Pimcore::getContainer()->get('cmf.activity_store');

        if(!( $activity->cmfGetActivityDate() instanceof Carbon)) {
            throw new \Exception(get_class($activity) . '::cmfGetActivityDate() needs to return a \Carbon\Carbon instance');
        }

        if(!$activity->getCustomer() instanceof CustomerInterface) {
            $store->deleteActivity($activity);
            return;
        }


        \Pimcore::getContainer()->get('cmf.segment_manager')->addCustomerToChangesQueue($activity->getCustomer());

        if(!$activity->cmfIsActive()) {
            $store->deleteActivity($activity);
            return;
        }

        if($entry = $store->getEntryForActivity($activity)) {
            $store->updateActivityInStore($activity, $entry);
        } else {
            $entry = $store->insertActivityIntoStore($activity);

            if( !$this->isDisableEvents() ) {
                $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\NewActivity($activity->getCustomer());
                $event->setActivity($activity);
                $event->setEntry( $entry );
                \Pimcore::getEventDispatcher()->dispatch($event->getName(), $event);
            }
        }

        if( !$this->isDisableEvents() ) {
            $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\AfterTrackActivity($activity->getCustomer());
            $event->setActivity($activity);
            $event->setEntry( $entry );
            \Pimcore::getEventDispatcher()->dispatch($event->getName(), $event);
        }
    }

    /**
     * Delete an activity from the activty store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */

    public function deleteActivity(ActivityInterface $activity)
    {

        $store = \Pimcore::getContainer()->get('cmf.activity_store');

        $store->deleteActivity($activity);
    }
}