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
use CustomerManagementFrameworkBundle\Model\ActivityExternalIdInterface;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityList\MySqlActivityList;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Doctrine\DBAL\Connection;
use Pimcore\Db;
use Pimcore\Model\DataObject\Concrete;

class MySQL extends SqlActivityStore implements ActivityStoreInterface
{
    protected function getAttributeInsertData(ActivityInterface $activity)
    {
        $activityData = $activity->cmfToArray();

        return \json_encode($activityData);
    }

    protected function getActivityStoreConnection()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '3.3.2',
            'The SqlActivityStore::getActivityStoreConnection() method is deprecated, use Db::get() instead.'
        );
        /** @var Connection $db */
        $db = Db::get();

        return $db;
    }

    /**
     * @return MySqlActivityList
     */
    public function getActivityList()
    {
        return new MySqlActivityList();
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface|null
     *
     * @throws \Exception
     */
    public function getEntryForActivity(ActivityInterface $activity)
    {
        $db = Db::get();

        $row = false;
        if ($activity instanceof Concrete) {
            $row = $db->fetchAssociative(
                'select * from '.self::ACTIVITIES_TABLE.' where o_id = ? order by id desc LIMIT 1 ',
                [$activity->getId()]
            );
        } elseif ($activity instanceof ActivityExternalIdInterface) {
            if (!$activity->getId()) {
                return null;
            }

            $row = $db->fetchAssociative(
                'select * from '.self::ACTIVITIES_TABLE.' where a_id = ? AND type = ? order by id desc LIMIT 1 ',
                [$activity->getId(), $activity->cmfGetType()]
            );
        }

        if (!is_array($row)) {
            return null;
        }

        $entry = $this->createEntryInstance($row);

        return $entry;
    }

    public function getActivityDataForCustomer(CustomerInterface $customer)
    {
        $db = Db::get();

        $result = $db->fetchAllAssociative(
            'select id,activityDate,type,o_id,a_id,md5,creationDate,modificationDate,attributes from '.self::ACTIVITIES_TABLE.' where customerId = ? order by activityDate asc',
            [$customer->getId()]
        );

        foreach ($result as $key => $value) {
            if ($value['attributes']) {
                $result[$key]['attributes'] = json_decode($value['attributes'], true);
            }
        }

        return $result;
    }

    /**
     * @param int $id
     *
     * @return ActivityStoreEntryInterface|null
     *
     * @throws \Exception
     */
    public function getEntryById($id)
    {
        $db = Db::get();

        if ($row = $db->fetchAssociative(
            'SELECT * FROM ' . self::ACTIVITIES_TABLE . ' WHERE id = ?',
            [$id]
        )) {
            return $this->createEntryInstance($row);
        }

        return null;
    }

    public function getActivitiesDataForWebservice($pageSize, $page, ExportActivitiesFilterParams $params)
    {
        $db = Db::get();

        $select = $db->createQueryBuilder()
            ->from(self::ACTIVITIES_TABLE)
            ->select('id',
                'customerId',
                'activityDate',
                'type',
                'implementationClass',
                'o_id',
                'a_id',
                'md5',
                'creationDate',
                'modificationDate',
                'attributes')
            ->addOrderBy('activityDate', 'asc');

        if ($ts = $params->getModifiedSinceTimestamp()) {
            $select->where('modificationDate >= ?');
            $select->setParameters([$ts]);
        }

        $paginator = $this->paginator->paginate($select, $page, $pageSize);

        $items = $paginator->getItems();
        foreach ($items as &$value) {
            $value = $this->createEntryInstance($value);
        }
        $paginator->setItems($items);

        return $paginator;
    }

    /**
     * @inheritdoc
     */
    public function lazyLoadMetadataOfEntry(ActivityStoreEntryInterface $entry)
    {
        if (!$entry->getId()) {
            return;
        }

        try {
            $rows = Db::get()->fetchAllAssociative(
                'SELECT * FROM ' . self::ACTIVITIES_METADATA_TABLE . ' WHERE activityId = ?',
                [$entry->getId()]
            );
        } catch (\Exception $e) {
            $this->getLogger()->error('fetching of activity store metadata failed: ' . $e->getMessage());
            $rows = [];
        }

        $metadata = [];

        foreach ($rows as $row) {
            $metadata[$row['key']] = $row['data'];
        }

        $entry->setMetadata($metadata);
    }
}
