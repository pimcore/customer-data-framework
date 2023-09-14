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
use CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Pimcore\Db;
use Pimcore\Db\Helper;
use Pimcore\Model\DataObject\Concrete;

class MariaDb extends SqlActivityStore implements ActivityStoreInterface
{
    protected function getActivityStoreConnection()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '3.3.2',
            'The SqlActivityStore::getActivityStoreConnection() method is deprecated, use Db::get() instead.'
        );

        return \CustomerManagementFrameworkBundle\Service\MariaDb::getInstance();
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface
     */
    public function insertActivityIntoStore(ActivityInterface $activity)
    {
        $entry = $this->createEntryInstance(
            [
                'customerId' => $activity->getCustomer()->getId(),
                'type' => $activity->cmfGetType(),
                'implementationClass' => get_class($activity),
                'o_id' => $activity instanceof Concrete ? $activity->getId() : null,
                'a_id' => $activity instanceof ActivityExternalIdInterface ? $activity->getId() : null,
                'activityDate' => $activity->cmfGetActivityDate()->getTimestamp(),
            ]
        );

        $this->saveActivityStoreEntry($entry, $activity);

        return $entry;
    }

    /**
     * @param ActivityInterface $activity
     * @param ActivityStoreEntryInterface $entry
     *
     * @return ActivityStoreEntryInterface
     *
     * @throws \Exception
     */
    public function updateActivityInStore(ActivityInterface $activity, ActivityStoreEntryInterface $entry = null)
    {
        if (is_null($entry)) {
            $entry = $this->getEntryForActivity($activity);
        }

        if (!$entry instanceof ActivityStoreEntryInterface) {
            throw new \Exception('ActivityStoreEntry not found for given activity');
        }

        $this->saveActivityStoreEntry($entry, $activity);

        return $entry;
    }

    public function updateActivityStoreEntry(ActivityStoreEntryInterface $entry, $updateAttributes = false)
    {
        if (!$entry->getId()) {
            throw new \Exception('updating only allowed for existing activity store entries');
        }

        if (!$updateAttributes) {
            $this->saveActivityStoreEntry($entry);

            return;
        }

        if ($activity = $entry->getRelatedItem()) {
            $this->saveActivityStoreEntry($entry, $activity);

            return;
        }

        $this->saveActivityStoreEntry($entry);
    }

    protected function saveActivityStoreEntry(ActivityStoreEntryInterface $entry, ActivityInterface $activity = null)
    {
        $db = Db::get();
        $time = time();
        $data = $entry->getData();

        unset($data['attributes']);
        if (!is_null($activity)) {
            $data['attributes'] = $this->getAttributeInsertData($activity);
            $data['type'] = $db->quote($activity->cmfGetType());
            $data['implementationClass'] = $db->quote(get_class($activity));
        } else {
            $data['type'] = $db->quote($data['type']);
            $data['implementationClass'] = $db->quote($data['implementationClass']);
        }

        $data['a_id'] = $db->quote($data['a_id']);

        if ($activity instanceof ActivityExternalIdInterface) {
            $data['a_id'] = $db->quote($activity->getId());
        }

        $data['customerId'] = !is_null($activity) ? $activity->getCustomer()->getId() : $entry->getCustomerId();

        $md5Data = [
            'customerId' => $data['customerId'],
            'type' => $data['type'],
            'implementationClass' => $data['implementationClass'],
            'o_id' => $data['o_id'],
            'a_id' => $data['a_id'],
            'activityDate' => $data['activityDate'],
            'attributes' => $data['attributes'],
        ];

        $data['md5'] = $db->quote(md5(serialize($md5Data)));
        $data['modificationDate'] = $time;

        $db->beginTransaction();

        try {
            if ($entry->getId()) {
                \CustomerManagementFrameworkBundle\Service\MariaDb::update(
                    self::ACTIVITIES_TABLE,
                    $data,
                    'id = '.$entry->getId()
                );
            } else {
                $data['creationDate'] = $time;
                $id = \CustomerManagementFrameworkBundle\Service\MariaDb::insert(
                    self::ACTIVITIES_TABLE,
                    $data
                );
                $entry->setId($id);
            }

            try {
                $db->executeQuery('DELETE FROM ' . self::ACTIVITIES_METADATA_TABLE . ' WHERE activityId = ?', [(int)$entry->getId()]);

                foreach ($entry->getMetadata() as $key => $data) {
                    $insertData = [
                        'activityId' => $entry->getId(),
                        'key' => $key,
                        'data' => $data
                    ];

                    $insertData = Helper::quoteDataIdentifiers($db, $insertData);

                    $db->insert(self::ACTIVITIES_METADATA_TABLE, $insertData);
                }
            } catch (TableNotFoundException $ex) {
                $this->getLogger()->error(sprintf('table %s not found - please press the update button of the CMF bundle in the extension manager', self::ACTIVITIES_METADATA_TABLE));
                $db->rollBack();
            }

            $db->commit();
        } catch (\Exception $e) {
            $this->getLogger()->error(sprintf('save activity (%s) failed: %s', $entry->getId(), $e->getMessage()));
            $db->rollBack();
        }
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface|null
     */
    public function getEntryForActivity(ActivityInterface $activity)
    {
        $db = Db::get();

        $row = false;
        if ($activity instanceof Concrete) {
            $row = $db->fetchAssociative(
                'select *, column_json(attributes) as attributes from '.self::ACTIVITIES_TABLE.' where o_id = ? order by id desc LIMIT 1 ',
                [$activity->getId()]
            );
        } elseif ($activity instanceof ActivityExternalIdInterface) {
            if (!$activity->getId()) {
                return null;
            }

            $row = $db->fetchAssociative(
                'select *, column_json(attributes) as attributes from '.self::ACTIVITIES_TABLE.' where a_id = ? AND type = ? order by id desc LIMIT 1 ',
                [$activity->getId(), $activity->cmfGetType()]
            );
        }

        if (!is_array($row)) {
            return null;
        }

        $entry = $this->createEntryInstance($row);

        return $entry;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getActivityDataForCustomer(CustomerInterface $customer)
    {
        $db = Db::get();

        $result = $db->fetchAllAssociative(
            'select id,activityDate,type,o_id,a_id,md5,creationDate,modificationDate,COLUMN_JSON(attributes) as attributes from '.self::ACTIVITIES_TABLE.' where customerId = ? order by activityDate asc',
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
     * @return DefaultMariaDbActivityList
     */
    public function getActivityList()
    {
        return new DefaultMariaDbActivityList();
    }

    /**
     * @param int $pageSize
     * @param int $page
     * @param ExportActivitiesFilterParams $params
     *
     * @return PaginationInterface
     */
    public function getActivitiesDataForWebservice($pageSize, $page, ExportActivitiesFilterParams $params)
    {
        $db = Db::get();

        $select = $db->createQueryBuilder();
        $select
            ->from(self::ACTIVITIES_TABLE)
            ->select(
                'id',
                'customerId',
                'activityDate',
                'type',
                'implementationClass',
                'o_id',
                'a_id',
                'md5',
                'creationDate',
                'modificationDate',
                'COLUMN_JSON(attributes) as attributes'
            )
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
     * @param string $entityType
     * @param int $deletionsSinceTimestamp
     *
     * @return array
     */
    public function getDeletionsData($entityType, $deletionsSinceTimestamp)
    {
        $db = Db::get();

        $sql = 'select * from '.self::DELETIONS_TABLE.' where entityType = '.$db->quote(
                $entityType
            ).' and creationDate >= '.$db->quote($deletionsSinceTimestamp);

        $data = $db->fetchAllAssociative($sql);

        foreach ($data as $key => $value) {
            $data[$key]['id'] = intval($data[$key]['id']);
            $data[$key]['creationDate'] = intval($data[$key]['creationDate']);
        }

        return [
            'data' => $data,
        ];
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return bool
     */
    public function deleteActivity(ActivityInterface $activity)
    {
        if (!$entry = $this->getEntryForActivity($activity)) {
            return false;
        }

        $this->deleteEntry($entry);

        return true;
    }

    /**
     * @param ActivityStoreEntryInterface $entry
     *
     * @return void
     */
    public function deleteEntry(ActivityStoreEntryInterface $entry)
    {
        $db = Db::get();
        $db->beginTransaction();

        try {
            $db->executeQuery('DELETE FROM '.self::ACTIVITIES_TABLE.' WHERE id = ?', [$entry->getId()]);

            Helper::upsert(
                $db,
                self::DELETIONS_TABLE,
                [
                    'id' => $entry->getId(),
                    'creationDate' => time(),
                    'entityType' => 'activities',
                    'type' => $entry->getType(),
                ],
                $this->getPrimaryKey($db, self::DELETIONS_TABLE)
            );

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw new $e;
        }
    }

    /**
     * @param CustomerInterface $customer
     */
    public function deleteCustomer(CustomerInterface $customer)
    {
        $db = Db::get();
        $db->exec(
            sprintf('delete from %s where customerId = %d', self::ACTIVITIES_TABLE, $customer->getId())
        );
    }

    /**
     * @param int $id
     *
     * @return ActivityStoreEntryInterface|null
     */
    public function getEntryById($id)
    {
        $db = Db::get();

        if ($row = $db->fetchAssociative(
            sprintf('select *, column_json(attributes) as attributes from %s where id = ?', self::ACTIVITIES_TABLE),
            [$id]
        )
        ) {
            return $this->createEntryInstance($row);
        }

        return null;
    }

    /**
     * @param CustomerInterface $customer
     * @param string|null $activityType
     *
     * @return int
     */
    public function countActivitiesOfCustomer(CustomerInterface $customer, $activityType = null)
    {
        $db = Db::get();

        $and = '';
        if ($activityType) {
            $and = ' and type='.$db->quote($activityType);
        }

        return (int) $db->fetchOne(
            'select count(id) from '.self::ACTIVITIES_TABLE." where customerId = ? $and",
            [$customer->getId()]
        );
    }

    /**
     * @param string $operator
     * @param string $type
     * @param int $count
     *
     * @return array
     */
    public function getCustomerIdsMatchingActivitiesCount($operator, $type, $count)
    {
        $db = Db::get();

        $where = '';
        if ($type) {
            $where = ' where type='.$db->quote($type);
        }

        $operator = in_array($operator, ['<', '>', '=']) ? $operator : '=';

        $sql = 'select customerId from '.self::ACTIVITIES_TABLE.$where.' group by customerId having count(*) '.$operator.intval(
                $count
            );

        return $db->fetchFirstColumn($sql);
    }

    /**
     * @return array
     */
    public function getAvailableActivityTypes()
    {
        $sql = 'select distinct type from '.self::ACTIVITIES_TABLE;

        return Db::get()->fetchFirstColumn($sql);
    }

    protected function getAttributeInsertData(ActivityInterface $activity)
    {
        $attributes = $activity->cmfToArray();
        if (!is_array($attributes)) {
            throw new \Exception('cmfToArray() needs to return an associative array');
        }

        $dataTypes = [];
        if ($_dataTypes = $activity::cmfGetAttributeDataTypes()) {
            foreach ($_dataTypes as $field => $dataType) {
                if ($dataType == ActivityInterface::DATATYPE_STRING) {
                    $dataTypes[$field] = \CustomerManagementFrameworkBundle\Service\MariaDb::DYNAMIC_COLUMN_DATA_TYPE_CHAR;
                } elseif ($dataType == ActivityInterface::DATATYPE_DOUBLE) {
                    $dataTypes[$field] = \CustomerManagementFrameworkBundle\Service\MariaDb::DYNAMIC_COLUMN_DATA_TYPE_DOUBLE;
                } elseif ($dataType == ActivityInterface::DATATYPE_INTEGER) {
                    $dataTypes[$field] = \CustomerManagementFrameworkBundle\Service\MariaDb::DYNAMIC_COLUMN_DATA_TYPE_INTEGER;
                } elseif ($dataType == ActivityInterface::DATATYPE_BOOL) {
                    $dataTypes[$field] = \CustomerManagementFrameworkBundle\Service\MariaDb::DYNAMIC_COLUMN_DATA_TYPE_BOOLEAN;
                }
            }
        }

        return \CustomerManagementFrameworkBundle\Service\MariaDb::createDynamicColumnInsert(
            $attributes,
            $dataTypes
        );
    }

    /**
     * @param array $data
     *
     * @return ActivityStoreEntryInterface
     */
    public function createEntryInstance(array $data)
    {
        /**
         * @var ActivityStoreEntryInterface $entry
         */
        $entry = \Pimcore::getContainer()->get('cmf.activity_store_entry');
        $entry->setData($data);

        if (!$entry instanceof ActivityStoreEntryInterface) {
            throw new \Exception('Activity store entry needs to implement ActivityStoreEntryInterface');
        }

        return $entry;
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
