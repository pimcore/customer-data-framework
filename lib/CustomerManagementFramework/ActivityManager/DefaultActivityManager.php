<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFramework\ActivityManager;

use CustomerManagementFramework\Event\NewActivity;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Db;

class DefaultActivityManager implements ActivityManagerInterface
{

    /**
     * Add or update activity in the ActivityStore
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */
    
    public function trackActivity(ActivityInterface $activity) {

        $store = Factory::getInstance()->getActivityStore();

        if(!$activity->getCustomer() instanceof CustomerInterface) {
            $store->deleteActivity($activity);
            return;
        }


        Factory::getInstance()->getSegmentManager()->addCustomerToChangesQueue($activity->getCustomer());

        if(!$activity->cmfIsActive()) {
            $store->deleteActivity($activity);
            return;
        }

        if($entry = $store->getEntryForActivity($activity)) {
            $store->updateActivityInStore($activity, $entry);
        } else {
            $store->insertActivityIntoStore($activity);

            $event = new \CustomerManagementFramework\ActionTrigger\Event\NewActivity($activity->getCustomer());
            $event->setActivity($activity);
            \Pimcore::getEventManager()->trigger($event->getName(), $event);
        }

    }

    /**
     * Delete an activity from the activty store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */

    public function deleteActivity(ActivityInterface $activity) {

        $store = Factory::getInstance()->getActivityStore();

        $store->deleteActivity($activity);
    }
}