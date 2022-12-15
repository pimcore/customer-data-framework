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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder;

use Doctrine\DBAL\Connection;
use Pimcore\Logger;
use Pimcore\Model\DataObject\Service;
use Throwable;

/**
 * @inheritdoc
 */
class DefaultQueueBuilder implements QueueBuilderInterface
{
    /**
     * @var string
     */
    private $segmentAssignmentQueueTable = '';

    /**
     * @var Connection
     */
    private $db = null;

    public function __construct(string $segmentAssignmentQueueTable, Connection $db)
    {
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setDb($db);
    }

    /**
     * @return string
     */
    public function getSegmentAssignmentQueueTable(): string
    {
        return $this->segmentAssignmentQueueTable;
    }

    /**
     * @param string $segmentAssignmentQueueTable
     */
    public function setSegmentAssignmentQueueTable(string $segmentAssignmentQueueTable)
    {
        $this->segmentAssignmentQueueTable = $segmentAssignmentQueueTable;
    }

    /**
     * @return Connection
     */
    public function getDb(): Connection
    {
        return $this->db;
    }

    /**
     * @param Connection $db
     */
    public function setDb(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function enqueue(string $elementId, string $type): bool
    {
        try {
            $enqueueStatement = sprintf(
                'INSERT INTO %s (`elementId`, `elementType`) ' .
                'VALUES (%s, "%s") '.
                'ON DUPLICATE KEY UPDATE `elementId` = `elementId`',
                $this->getSegmentAssignmentQueueTable(), $elementId, $type);

            $this->getDb()->executeQuery($enqueueStatement);

            return true;
        } catch (Throwable $exception) {
            Logger::error($exception->getMessage());

            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function enqueueChildren(string $elementId, string $type): bool
    {
        try {
            $objectIdField = Service::getVersionDependentDatabaseColumnName('id');
            $objectPathField = Service::getVersionDependentDatabaseColumnName('path');
            $objectKeyField = Service::getVersionDependentDatabaseColumnName('key');
            $formatArguments = [
                1 => $this->getSegmentAssignmentQueueTable(),
                2 => $type === 'object' ? $objectIdField : 'id',
                3 => $type . 's',
                4 => $type === 'object' ? $objectPathField : 'path',
                5 => $type === 'object' ? $objectKeyField : ($type === 'asset' ? 'filename' : 'key'),
            ];

            $enqueueStatement = vsprintf(
                'INSERT INTO `%1$s` (`elementId`, `elementType`) ' .
                'SELECT `%2$s` AS elementId, :elementType AS elementType FROM `%3$s` ' .
                'WHERE `%4$s` LIKE CONCAT( ' .
                '(SELECT CONCAT(`%4$s`, `%5$s`) FROM `%3$s` WHERE `%2$s` = :elementId)' .
                ', "%%") ON DUPLICATE KEY UPDATE `elementId` = `elementId`; ',
                $formatArguments);

            $this->getDb()->beginTransaction();

            $this->getDb()->executeQuery($enqueueStatement,
                [
                    'elementType' => $type,
                    'elementId' => (int) $elementId
                ]);

            $this->getDb()->commit();

            return true;
        } catch (Throwable $exception) {
            Logger::error($exception->getMessage());

            return false;
        }
    }
}
