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
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Db;
use Pimcore\Model\DataObject\Concrete;
use Zend\Paginator\Paginator;

class MariaDb extends SqlActivityStore
{
    protected function getActivityStoreConnection()
    {
        return \CustomerManagementFrameworkBundle\Service\MariaDb::getInstance();
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

        return \CustomerManagementFrameworkBundle\Service\MariaDb::getInstance()->createDynamicColumnInsert(
            $attributes,
            $dataTypes
        );
    }
}
