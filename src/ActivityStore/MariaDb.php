<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\ActivityStore;

use CustomerManagementFrameworkBundle\Filter\ExportActivitiesFilterParams;
use CustomerManagementFrameworkBundle\Model\ActivityExternalIdInterface;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Db;
use Pimcore\Model\DataObject\Concrete;
use Zend\Paginator\Paginator;

class MariaDb implements ActivityStoreInterface
{
    const ACTIVITIES_TABLE = 'plugin_cmf_activities';
    const DELETIONS_TABLE = 'plugin_cmf_deletions';

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

        $activity = null;
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
            $data['attributes'] = $this->getActivityAttributeDataAsDynamicColumnInsert($activity);
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

        if ($entry->getId()) {
            \CustomerManagementFrameworkBundle\Service\MariaDb::getInstance()->update(
                self::ACTIVITIES_TABLE,
                $data,
                'id = '.$entry->getId()
            );
        } else {
            $data['creationDate'] = $time;
            $id = \CustomerManagementFrameworkBundle\Service\MariaDb::getInstance()->insert(
                self::ACTIVITIES_TABLE,
                $data
            );
            $entry->setId($id);
        }
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return bool|ActivityStoreEntryInterface
     */
    public function getEntryForActivity(ActivityInterface $activity)
    {
        $db = Db::get();

        $row = false;
        if ($activity instanceof Concrete) {
            $row = $db->fetchRow(
                'select *, column_json(attributes) as attributes from '.self::ACTIVITIES_TABLE.' where o_id = ? order by id desc LIMIT 1 ',
                $activity->getId()
            );
        } elseif ($activity instanceof ActivityExternalIdInterface) {
            if (!$activity->getId()) {
                return false;
            }

            $row = $db->fetchRow(
                'select *, column_json(attributes) as attributes from '.self::ACTIVITIES_TABLE.' where a_id = ? AND type = ? order by id desc LIMIT 1 ',
                [$activity->getId(), $activity->cmfGetType()]
            );
        }

        if (!is_array($row)) {
            return false;
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

        $result = $db->fetchAll(
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
     * @param                              $pageSize
     * @param int $page
     * @param ExportActivitiesFilterParams $params
     *
     * @return Paginator
     */
    public function getActivitiesDataForWebservice($pageSize, $page = 1, ExportActivitiesFilterParams $params)
    {
        $db = Db::get();

        $select = $db->select();
        $select
            ->from(
                self::ACTIVITIES_TABLE,
                [
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
                    'attributes' => 'COLUMN_JSON(attributes)',
                ]
            )
            ->order('activityDate asc');

        if ($ts = $params->getModifiedSinceTimestamp()) {
            $select->where('modificationDate >= ?', $ts);
        }

        $paginator = new Paginator(new Db\ZendCompatibility\QueryBuilder\PaginationAdapter($select));
        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($page);

        foreach ($paginator as &$value) {
            $value = $this->createEntryInstance($value);
        }

        return $paginator;
    }

    /**
     * @param $entityType
     * @param $deletionsSinceTimestamp
     *
     * @return array
     */
    public function getDeletionsData($entityType, $deletionsSinceTimestamp)
    {
        $db = Db::get();

        $sql = 'select * from '.self::DELETIONS_TABLE.' where entityType = '.$db->quote(
                $entityType
            ).' and creationDate >= '.$db->quote($deletionsSinceTimestamp);

        $data = $db->fetchAll($sql);

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
            $db->query('delete from '.self::ACTIVITIES_TABLE.' where id = '.$entry->getId());

            $db->insertOrUpdate(
                self::DELETIONS_TABLE,
                [
                    'id' => $entry->getId(),
                    'creationDate' => time(),
                    'entityType' => 'activities',
                    'type' => $entry->getType(),
                ]
            );

            $db->commit();
        } catch (\Exception $e) {
            $e->rollback();

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
     * @param $id
     *
     * @return ActivityStoreEntryInterface
     */
    public function getEntryById($id)
    {
        $db = Db::get();

        if ($row = $db->fetchRow(
            sprintf('select *, column_json(attributes) as attributes from %s where id = ?', self::ACTIVITIES_TABLE),
            $id
        )
        ) {
            return $this->createEntryInstance($row);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param null $activityType
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
            $customer->getId()
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

        return $db->fetchCol($sql);
    }

    /**
     * @return array
     */
    public function getAvailableActivityTypes()
    {
        $sql = 'select distinct type from '.self::ACTIVITIES_TABLE;

        return Db::get()->fetchCol($sql);
    }

    protected function getActivityAttributeDataAsDynamicColumnInsert(ActivityInterface $activity)
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

        return \CustomerManagementFrameworkBundle\Service\MariaDb::getInstance()->createDynamicColumnInsert(
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
}
