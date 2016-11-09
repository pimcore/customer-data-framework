<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 12.10.2016
 * Time: 13:30
 */

namespace CustomerManagementFramework\ActivityStore;

use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;

interface ActivityStoreInterface {

    /**
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function insertActivityIntoStore(ActivityInterface $activity);

    /**
     * @param ActivityInterface           $activity
     * @param ActivityStoreEntryInterface $entry
     *
     * @return void
     */
    public function updateActivityInStore(ActivityInterface $activity, ActivityStoreEntryInterface $entry);

    /**
     * @param ActivityInterface $activity
     *
     * @return array
     * @return array
     */
    public function getEntryForActivity(ActivityInterface $activity);

    public function getActivityDataForCustomer(CustomerInterface $customer);

    public function getActivityList();

    public function deleteActivity(ActivityInterface $activity);

    public function deleteCustomer(CustomerInterface $customer);

    public function getActivitiesData($pageSize, $page = 1, ExportActivitiesFilterParams $params);

    public function getDeletionsData($type, $deletionsSinceTimestamp);


}