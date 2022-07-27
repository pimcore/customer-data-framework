<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\ActivityStore;

use CustomerManagementFrameworkBundle\Filter\ExportActivitiesFilterParams;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityList\ActivityListInterface;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface ActivityStoreInterface
 *
 * @package CustomerManagementFramework\ActivityStore
 */
interface ActivityStoreInterface
{
    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface
     */
    public function insertActivityIntoStore(ActivityInterface $activity);

    /**
     * @param ActivityInterface $activity
     * @param ActivityStoreEntryInterface|null $entry
     *
     * @return ActivityStoreEntryInterface
     */
    public function updateActivityInStore(ActivityInterface $activity, ActivityStoreEntryInterface $entry = null);

    /**
     *
     * @param ActivityStoreEntryInterface $entry
     * @param bool $updateAttributes
     *
     * @return void
     */
    public function updateActivityStoreEntry(ActivityStoreEntryInterface $entry, $updateAttributes = false);

    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface|null
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
     * @return bool
     */
    public function deleteActivity(ActivityInterface $activity);

    /**
     * @param int $id
     *
     * @return ActivityStoreEntryInterface|null
     */
    public function getEntryById($id);

    /**
     * @param ActivityStoreEntryInterface $entry
     *
     * @return void
     */
    public function deleteEntry(ActivityStoreEntryInterface $entry);

    /**
     * Deletes all activities for $customer in the store.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function deleteCustomer(CustomerInterface $customer);

    /**
     * @param int $pageSize
     * @param int $page
     * @param ExportActivitiesFilterParams $params
     *
     * @return PaginationInterface
     */
    public function getActivitiesDataForWebservice($pageSize, $page, ExportActivitiesFilterParams $params);

    /**
     * @param string $type
     * @param int $deletionsSinceTimestamp
     *
     * @return array
     */
    public function getDeletionsData($type, $deletionsSinceTimestamp);

    /**
     * @param CustomerInterface $customer
     * @param string|null $activityType
     *
     * @return int
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

    /**
     * @param array $data
     *
     * @return ActivityStoreEntryInterface
     */
    public function createEntryInstance(array $data);

    /**
     * Metadata items of entries are lazy loaded. This method loads the metadata items from the store.
     *
     * @param ActivityStoreEntryInterface $entry
     *
     * @return void
     */
    public function lazyLoadMetadataOfEntry(ActivityStoreEntryInterface $entry);
}
