<?php

namespace CustomerManagementFrameworkBundle\ActivityStore;

use CustomerManagementFrameworkBundle\Filter\ExportActivitiesFilterParams;
use CustomerManagementFrameworkBundle\Model\ActivityExternalIdInterface;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityList\MySqlActivityList;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Db;
use Pimcore\Model\DataObject\Concrete;
use Zend\Paginator\Paginator;

class MySQL extends SqlActivityStore implements ActivityStoreInterface
{
    protected function getAttributeInsertData(ActivityInterface $activity)
    {
        $activityData = $activity->cmfToArray();
        return \json_encode($activityData);
    }

    protected function getActivityStoreConnection()
    {
        return Db::get();
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
     * @return null|ActivityStoreEntryInterface
     * @throws \Exception
     */
    public function getEntryForActivity(ActivityInterface $activity)
    {
        $db = Db::get();

        $row = false;
        if ($activity instanceof Concrete) {
            $row = $db->fetchRow(
                'select * from '.self::ACTIVITIES_TABLE.' where o_id = ? order by id desc LIMIT 1 ',
                $activity->getId()
            );
        } elseif ($activity instanceof ActivityExternalIdInterface) {
            if (!$activity->getId()) {
                return null;
            }

            $row = $db->fetchRow(
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

        $result = $db->fetchAll(
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
     * @param $id
     *
     * @return null|ActivityStoreEntryInterface
     * @throws \Exception
     */
    public function getEntryById($id)
    {
        $db = Db::get();

        if ($row = $db->fetchRow(
            sprintf('select * from %s where id = ?', self::ACTIVITIES_TABLE),
            $id
        )) {
            return $this->createEntryInstance($row);
        }

        return null;
    }

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
                    'attributes',
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
     * @inheritdoc
     */
    public function lazyLoadMetadataOfEntry(ActivityStoreEntryInterface $entry)
    {
        if(!$entry->getId()) {
            return;
        }

        try {
            $rows = Db::get()->fetchAll(sprintf('select * from %s where activityId = %s',
                self::ACTIVITIES_METADATA_TABLE,
                $entry->getId()
            ));
        } catch(\Exception $e) {
            $this->getLogger()->error('fetching of activity store metadata failed: ' . $e->getMessage());
            $rows = [];
        }


        $metadata = [];

        foreach($rows as $row) {
            $metadata[$row['key']] = $row['data'];
        }

        $entry->setMetadata($metadata);
    }
}