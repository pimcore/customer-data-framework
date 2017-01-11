<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 12.10.2016
 * Time: 13:30
 */

namespace CustomerManagementFramework\ActivityStore;

use CustomerManagementFramework\ActivityList\ActivityListInterface;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;

/**
 * Interface ActivityStoreInterface
 *
 * @package CustomerManagementFramework\ActivityStore
 */
interface ActivityStoreInterface {

    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface
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
     * @return ActivityStoreEntryInterface
     */
    public function getEntryForActivity(ActivityInterface $activity);

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getActivityDataForCustomer(CustomerInterface $customer);

    /**
     * @return ActivityListInterface
     */
    public function getActivityList();

    /**
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function deleteActivity(ActivityInterface $activity);

    /**
     * Deletes all activities for $customer in the store.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function deleteCustomer(CustomerInterface $customer);

    /**
     * @param                              $pageSize
     * @param int                          $page
     * @param ExportActivitiesFilterParams $params
     *
     * @return \Zend_Paginator
     */
    public function getActivitiesDataForWebservice($pageSize, $page = 1, ExportActivitiesFilterParams $params);

    /**
     * @param $type
     * @param $deletionsSinceTimestamp
     *
     * @return mixed
     */
    public function getDeletionsData($type, $deletionsSinceTimestamp);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getEntryById($id);

    /**
     * @param ActivityStoreEntryInterface $entry
     *
     * @return void
     */
    public function deleteEntry(ActivityStoreEntryInterface $entry);

    /**
     * @param CustomerInterface $customer
     * @param null              $activityType
     *
     * @return mixed
     */
    public function countActivitiesOfCustomer(CustomerInterface $customer, $activityType = null);

    /**
     *
     * @param string $operator (>,< or =)
     * @param string $type
     * @param int $count
     *
     * @return array
     */
    public function getCustomerIdsMatchingActivitiesCount($operator, $type, $count);

    /**
     * @return array
     */
    public function getAvailableActivityTypes();
}