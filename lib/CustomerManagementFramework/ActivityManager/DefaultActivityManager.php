<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFramework\ActivityManager;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\IActivity;
use CustomerManagementFramework\Model\ICustomer;
use Pimcore\Db;

class DefaultActivityManager implements IActivityManager
{

    /**
     * @param IActivity $activity
     *
     * @return void
     */
    
    public function trackActivity(IActivity $activity) {

        $store = Factory::getInstance()->getActivityStore();

        if(!$activity->getCustomer() instanceof ICustomer) {
            $store->deleteActivity($activity);
            return;
        }

        if(!$activity->cmfIsActive()) {
            $store->deleteActivity($activity);
            return;
        }

        if($entry = $store->getEntryForActivity($activity)) {
            $store->updateActivityInStore($activity, $entry);
        } else {
            $store->insertActivityIntoStore($activity);
        }

    }

    /**
     * @param IActivity $activity
     *
     * @return void
     */

    public function deleteActivity(IActivity $activity) {

        $store = Factory::getInstance()->getActivityStore();

        $store->deleteActivity($activity);
    }
}