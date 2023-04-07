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

use CustomerManagementFrameworkBundle\Model\ActivityExternalIdInterface;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use CustomerManagementFrameworkBundle\Traits\PrimaryKeyTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Db;
use Pimcore\Db\Helper;
use Pimcore\Model\DataObject\Concrete;

abstract class SqlActivityStore
{
    use LoggerAware;
    use PrimaryKeyTrait;

    const ACTIVITIES_TABLE = 'plugin_cmf_activities';
    const ACTIVITIES_METADATA_TABLE = 'plugin_cmf_activities_metadata';
    const DELETIONS_TABLE = 'plugin_cmf_deletions';

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @param PaginatorInterface $paginator
     */
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

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
     *
     * @return mixed any data which the activity repository / service can save
     */
    abstract protected function getAttributeInsertData(ActivityInterface $activity);

    /**
     * @return Connection
     *
     * @deprecated
     */
    abstract protected function getActivityStoreConnection();

    protected function saveActivityStoreEntry(ActivityStoreEntryInterface $entry, ActivityInterface $activity = null)
    {
        $db = Db::get();
        $time = time();
        $data = $entry->getData();

        if (null !== $activity) {
            $data['attributes'] = $this->getAttributeInsertData($activity);
            $data['type'] = $activity->cmfGetType();
            $data['implementationClass'] = get_class($activity);
        }

        if ($activity instanceof ActivityExternalIdInterface) {
            $data['a_id'] = $activity->getId();
        }

        $data['customerId'] = null !== $activity ? $activity->getCustomer()->getId() : $entry->getCustomerId();

        $md5Data = [
            'customerId' => $data['customerId'],
            'type' => $data['type'],
            'implementationClass' => $data['implementationClass'],
            'o_id' => $data['o_id'],
            'a_id' => $data['a_id'],
            'activityDate' => $data['activityDate'],
            'attributes' => $data['attributes'],
        ];

        $data['md5'] = md5(serialize($md5Data));
        $data['modificationDate'] = $time;

        $db->beginTransaction();

        try {
            if ($entry->getId()) {
                $data = Helper::quoteDataIdentifiers($db, $data);
                $db->update(
                    self::ACTIVITIES_TABLE,
                    $data,
                    ['id' => $entry->getId()]
                );
            } else {
                $data['creationDate'] = $time;
                $data = Helper::quoteDataIdentifiers($db, $data);
                $db->insert(
                    self::ACTIVITIES_TABLE,
                    $data
                );
                $entry->setId((int) $db->lastInsertId());
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
     * @return array
     */
    public function getAvailableActivityTypes()
    {
        $sql = 'select distinct type from '.self::ACTIVITIES_TABLE;

        return Db::get()->fetchFirstColumn($sql);
    }

    /**
     * @param ActivityInterface                $activity
     * @param ActivityStoreEntryInterface|null $entry
     *
     * @return null|ActivityStoreEntryInterface
     *
     * @throws \Exception
     */
    public function updateActivityInStore(ActivityInterface $activity, ActivityStoreEntryInterface $entry = null)
    {
        if (null === $entry) {
            $entry = $this->getEntryForActivity($activity);
        }

        if (!$entry instanceof ActivityStoreEntryInterface) {
            throw new \Exception('ActivityStoreEntry not found for given activity');
        }

        $this->saveActivityStoreEntry($entry, $activity);

        return $entry;
    }

    /**
     * @param ActivityStoreEntryInterface $entry
     * @param bool                        $updateAttributes
     *
     * @throws \Exception
     */
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

    /**
     * @param array $data
     *
     * @return ActivityStoreEntryInterface
     *
     * @throws \Exception
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
        if ($type) {
            $where = ' where type='.$db->quote($type);
        }

        $operator = in_array($operator, ['<', '>', '='], true) ? $operator : '=';

        $sql = 'select customerId from '.self::ACTIVITIES_TABLE.$where.' group by customerId having count(*) '.$operator.(int)$count;

        return $db->fetchFirstColumn($sql);
    }

    /**
     * @param CustomerInterface $customer
     */
    public function deleteCustomer(CustomerInterface $customer)
    {
        $db = Db::get();
        $db->delete(self::ACTIVITIES_TABLE, ['customerId' => $customer->getId()]);
    }

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
            $db->rollBack();

            throw $e;
        }
    }

    /**
     * @param ActivityInterface $activity
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function deleteActivity(ActivityInterface $activity)
    {
        if (!$entry = $this->getEntryForActivity($activity)) {
            return false;
        }

        $this->deleteEntry($entry);

        return true;
    }

    public function getDeletionsData($entityType, $deletionsSinceTimestamp)
    {
        $db = Db::get();

        $sql = 'select * from '.self::DELETIONS_TABLE.' where entityType = '.$db->quote(
                $entityType
            ).' and creationDate >= '.$db->quote($deletionsSinceTimestamp);

        $data = $db->fetchAllAssociative($sql);

        foreach ($data as $key => $value) {
            $data[$key]['id'] = (int)$data[$key]['id'];
            $data[$key]['creationDate'] = (int)$data[$key]['creationDate'];
        }

        return [
            'data' => $data,
        ];
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
     * @param ActivityInterface $activity
     *
     * @return ActivityStoreEntryInterface|null
     */
    abstract public function getEntryForActivity(ActivityInterface $activity);
}
