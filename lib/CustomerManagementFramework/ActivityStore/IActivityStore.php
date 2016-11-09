<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 12.10.2016
 * Time: 13:30
 */

namespace CustomerManagementFramework\ActivityStore;

use CustomerManagementFramework\ActivityStoreEntry\IActivityStoreEntry;
use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Model\IActivity;
use CustomerManagementFramework\Model\ICustomer;

interface IActivityStore {

    /**
     * @param IActivity $activity
     *
     * @return void
     */
    public function insertActivityIntoStore(IActivity $activity);

    /**
     * @param IActivity $activity
     * @param IActivityStoreEntry $entry
     *
     * @return void
     */
    public function updateActivityInStore(IActivity $activity, IActivityStoreEntry $entry);

    /**
     * @param IActivity $activity
     *
     * @return array
     */
    public function getEntryForActivity(IActivity $activity);

    public function getActivityDataForCustomer(ICustomer $customer);

    public function getActivityList();

    public function deleteActivity(IActivity $activity);

    public function deleteCustomer(ICustomer $customer);

    public function getActivitiesData($pageSize, $page = 1, ExportActivitiesFilterParams $params);

    public function getDeletionsData($type, $deletionsSinceTimestamp);


}