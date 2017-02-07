<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\ActivityStore;

use CustomerManagementFramework\ActivityList\DefaultMariaDbActivityList;
use CustomerManagementFramework\ActivityStoreEntry\DefaultActivityStoreEntry;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Filter\ExportActivitiesFilterParams;
use CustomerManagementFramework\Model\ActivityExternalIdInterface;
use CustomerManagementFramework\Model\ActivityInterface;
use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Db;
use Pimcore\Model\Object\Concrete;

class MariaDb implements ActivityStoreInterface{


    const ACTIVITIES_TABLE = 'plugin_cmf_activities';
    const DELETIONS_TABLE = 'plugin_cmf_deletions';

    /**
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface
     */
    public function insertActivityIntoStore(ActivityInterface $activity) {

        $data = self::createDbRowData($activity);
        $data['creationDate'] = time();

        $id = \CustomerManagementFramework\Service\MariaDb::getInstance()->insert(self::ACTIVITIES_TABLE, $data);

        return self::getEntryById($id);
    }

    /**
     * @param ActivityInterface           $activity
     * @param ActivityStoreEntryInterface $entry
     */
    public function updateActivityInStore(ActivityInterface $activity, ActivityStoreEntryInterface $entry) {

        $data = self::createDbRowData($activity);

        \CustomerManagementFramework\Service\MariaDb::getInstance()->update(self::ACTIVITIES_TABLE, $data, "id = " . $entry->getId());
    }

    public function updateActivityStoreEntry(ActivityStoreEntryInterface $entry, $updateAttributes = false) {
        if(!$entry->getId()) {
            throw new \Exception("updateActivityStoreEntry only allowed for existing activity store entries");
        }

        if($updateAttributes) {
            $relatedItem = $entry->getRelatedItem();

            $this->updateActivityInStore($relatedItem, $entry);

            return;
        }

        $data = $entry->getData();
        unset($data['attributes']);

        $db = Db::get();

        $data['type'] = $db->quote($data['type']);
        $data['implementationClass'] = $db->quote($data['implementationClass']);
        $data['a_id'] = $db->quote($data['a_id']);

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
        $data['modificationDate'] = time();

        \CustomerManagementFramework\Service\MariaDb::getInstance()->update(self::ACTIVITIES_TABLE, $data, "id = " . $entry->getId());
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return bool|DefaultActivityStoreEntry
     */
    public function getEntryForActivity(ActivityInterface $activity)
    {
        $db = Db::get();

        $row = false;
        if($activity instanceof Concrete) {
            $row = $db->fetchRow("select *, column_json(attributes) as attributes from " . self::ACTIVITIES_TABLE . " where o_id = ? order by id desc LIMIT 1 ", $activity->getId());
        } elseif($activity instanceof ActivityExternalIdInterface) {
            $row = $db->fetchRow("select *, column_json(attributes) as attributes from " . self::ACTIVITIES_TABLE . " where a_id = ? order by id desc LIMIT 1 ", $activity->getId());
        }

        if(!is_array($row)) {
            return false;
        }


        $entry = Factory::getInstance()->createObject('CustomerManagementFramework\ActivityStoreEntry', ActivityStoreEntryInterface::class, ["data"=>$row]);

        return $entry;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return array
     */
    public function getActivityDataForCustomer(CustomerInterface $customer) {
        $db = Db::get();

        $result = $db->fetchAll("select id,activityDate,type,o_id,a_id,md5,creationDate,modificationDate,COLUMN_JSON(attributes) as attributes from " . self::ACTIVITIES_TABLE . " where customerId = ? order by activityDate asc", [$customer->getId()]);

        foreach($result as $key => $value) {
            if($value['attributes']) {
                $result[$key]['attributes'] = \Zend_Json::decode($value['attributes']);
            }
        }

        return $result;
    }

    /**
     * @return DefaultMariaDbActivityList
     */
    public function getActivityList() {
        return new DefaultMariaDbActivityList();
    }

    /**
     * @param                              $pageSize
     * @param int                          $page
     * @param ExportActivitiesFilterParams $params
     *
     * @return \Zend_Paginator
     */
    public function getActivitiesDataForWebservice($pageSize, $page = 1, ExportActivitiesFilterParams $params)
    {
        $db = Db::get();

        $select = $db->select();
        $select
            ->from(self::ACTIVITIES_TABLE,
                [
                    'id',
                    'activityDate',
                    'type',
                    'implementationClass',
                    'o_id',
                    'a_id',
                    'md5',
                    'creationDate',
                    'modificationDate',
                    'attributes' => 'COLUMN_JSON(attributes)'
                ]
                )
            ->order("activityDate asc")
        ;

        if($ts = $params->getModifiedSinceTimestamp()) {
            $select->where("modificationDate >= ?", $ts);
        }

        $paginator = new \Zend_Paginator(new \Zend_Paginator_Adapter_DbSelect($select));
        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($page);

        foreach($paginator as &$value) {
            $value = Factory::getInstance()->createObject('CustomerManagementFramework\ActivityStoreEntry', ActivityStoreEntryInterface::class, ["data"=>$value]);
        }

        return $paginator;
    }

    /**
     * @param $entityType
     * @param $deletionsSinceTimestamp
     *
     * @return array
     */
    public function getDeletionsData($entityType, $deletionsSinceTimestamp) {
        $db = Db::get();

        $sql = "select * from " . self::DELETIONS_TABLE . " where entityType = " . $db->quote($entityType) . " and creationDate >= " . $db->quote($deletionsSinceTimestamp);

        $data = $db->fetchAll($sql);

        foreach($data as $key => $value) {
            $data[$key]['id'] = intval($data[$key]['id']);
            $data[$key]['creationDate'] = intval($data[$key]['creationDate']);
        }

        return [
            'data' => $data
        ];
    }

    /**
     * @param ActivityInterface $activity
     */
    public function deleteActivity(ActivityInterface $activity) {

        $db = Db::get();
        $id = false;
        if($activity instanceof Concrete) {
            $id = $db->fetchOne("select id from " . self::ACTIVITIES_TABLE . " where o_id = ? limit 1", $activity->getId());
        } elseif(method_exists($activity, 'getId')) {
            $id = $db->fetchOne("select id from " . self::ACTIVITIES_TABLE . " where a_id = ? limit 1", $activity->getId());
        }

        if($id) {
            $entry = $this->getEntryById($id);
            $this->deleteEntry($entry);
        }
    }


    /**
     * @param ActivityStoreEntryInterface $entry
     *
     * @return void
     */
    public function deleteEntry(ActivityStoreEntryInterface $entry) {
        $db = Db::get();
        $db->beginTransaction();

        try {
            $db->query("delete from " . self::ACTIVITIES_TABLE . " where id = " . $entry->getId());

            $db->insertOrUpdate(self::DELETIONS_TABLE, [
                'id' => $entry->getId(),
                'creationDate' => time(),
                'entityType' => 'activities',
                'type' => $entry->getType()
            ]);

            $db->commit();
        } catch(\Exception $e) {
            $e->rollback();

            throw new $e;
        }
    }

    /**
     * @param CustomerInterface $customer
     */
    public function deleteCustomer(CustomerInterface $customer) {

    }

    /**
     * @param ActivityInterface $activity
     *
     * @return array
     */
    protected function createDbRowData(ActivityInterface $activity) {

        $db = Db::get();

        $time = time();

        $attributes = $activity->cmfToArray();

        $dataTypes = [];
        if($_dataTypes = $activity->cmfGetAttributeDataTypes()) {
            foreach($_dataTypes as $field => $dataType) {
                if($dataType == ActivityInterface::DATATYPE_STRING) {
                    $dataTypes[$field] = 'char';
                } elseif($dataType == ActivityInterface::DATATYPE_DOUBLE) {
                    $dataTypes[$field] = 'double';
                } elseif($dataType == ActivityInterface::DATATYPE_INTEGER) {
                    $dataTypes[$field] = 'int';
                }
            }
        }

        $data = [
            'customerId' => $activity->getCustomer()->getId(),
            'type' => $db->quote($activity->cmfGetType()),
            'implementationClass' => $db->quote(get_class($activity)),
            'o_id' => $activity instanceof Concrete ? $activity->getId() : null,
            'a_id' => $activity instanceof ActivityExternalIdInterface ? $db->quote($activity->getId()) : null,
            'activityDate' => $activity->cmfGetActivityDate()->getTimestamp(),
            'attributes' => \CustomerManagementFramework\Service\MariaDb::getInstance()->createDynamicColumnInsert($attributes, $dataTypes),
        ];

        $data['md5'] = $db->quote(md5(serialize($data)));
        $data['modificationDate'] = $time;

        return $data;
    }

    /**
     * @param $id
     *
     * @return ActivityStoreEntryInterface
     */
    public function getEntryById($id) {

        $db = Db::get();

        if($row = $db->fetchRow(sprintf("select *, column_json(attributes) as attributes from %s where id = ?", self::ACTIVITIES_TABLE), $id)) {
            return Factory::getInstance()->createObject('CustomerManagementFramework\ActivityStoreEntry', ActivityStoreEntryInterface::class, ["data"=>$row]);
        }
    }


    /**
     * @param CustomerInterface $customer
     * @param null              $activityType
     *
     * @return string
     */
    public function countActivitiesOfCustomer(CustomerInterface $customer, $activityType = null)
    {
        $db = Db::get();

        $and = '';
        if($activityType) {
            $and = ' and type=' . $db->quote($activityType);
        }

        return $db->fetchOne("select count(id) from " . self::ACTIVITIES_TABLE . " where customerId = ? $and", $customer->getId());
    }

    /**
     * @param string $operator
     * @param string $type
     * @param int    $count
     *
     * @return array
     */
    public function getCustomerIdsMatchingActivitiesCount($operator, $type, $count)
    {
        $db = Db::get();

        $where = '';
        if($type) {
            $where = ' where type=' . $db->quote($type);
        }

        $operator = in_array($operator, ['<','>','=']) ? $operator : '=';

        $sql = "select customerId from " . self::ACTIVITIES_TABLE . $where . " group by customerId having count(*) " .  $operator . intval($count);

        return $db->fetchCol($sql);
    }

    /**
     * @return array
     */
    public function getAvailableActivityTypes()
    {
        $sql = "select distinct type from " . self::ACTIVITIES_TABLE;

        return Db::get()->fetchCol($sql);
    }
}