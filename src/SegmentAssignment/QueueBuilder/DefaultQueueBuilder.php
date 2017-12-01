<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 23.10.2017
 * Time: 16:45
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder;

use Pimcore\Db\Connection;
use Pimcore\Logger;
use Throwable;

/**
 * @inheritdoc
 */
class DefaultQueueBuilder implements QueueBuilderInterface {

    /**
     * @var string
     */
    private $segmentAssignmentQueueTable = '';

    /**
     * @var Connection
     */
    private $db = null;

    public function __construct(string $segmentAssignmentQueueTable, Connection $db) {
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setDb($db);
    }

    /**
     * @return string
     */
    public function getSegmentAssignmentQueueTable(): string {
        return $this->segmentAssignmentQueueTable;
    }

    /**
     * @param string $segmentAssignmentQueueTable
     */
    public function setSegmentAssignmentQueueTable(string $segmentAssignmentQueueTable) {
        $this->segmentAssignmentQueueTable = $segmentAssignmentQueueTable;
    }

    /**
     * @return Connection
     */
    public function getDb(): Connection {
        return $this->db;
    }

    /**
     * @param Connection $db
     */
    public function setDb(Connection $db) {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function enqueue(string $elementId, string $type): bool {
        try {
            $enqueueStatement = sprintf(
                'INSERT INTO %s (`elementId`, `elementType`) ' .
                'VALUES (%s, "%s") '.
                'ON DUPLICATE KEY UPDATE `elementId` = `elementId`',
                $this->getSegmentAssignmentQueueTable(), $elementId, $type);

            $this->getDb()->query($enqueueStatement);

            return true;
        } catch(Throwable $exception) {
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
            $formatArguments = [
                1 => $this->getSegmentAssignmentQueueTable(),
                2 => $type === 'object' ? 'o_id' : 'id',
                3 => $type . 's',
                4 => $type === 'object' ? 'o_path' : 'path',
                5 => $type === 'object' ? 'o_key' : 'key',
            ];

            $enqueueStatement = vsprintf(
                'INSERT INTO `%1$s` (`elementId`, `elementType`) ' .
                'SELECT `%2$s` AS elementId, :elementType AS elementType FROM `%3$s` ' .
                'WHERE `%4$s` LIKE CONCAT( ' .
                '(SELECT CONCAT(`%4$s`, `%5$s`) FROM `%3$s` WHERE `%2$s` = :elementId)' .
                ', "%%") ON DUPLICATE KEY UPDATE `elementId` = `elementId`; ',
                $formatArguments);

            $this->getDb()->beginTransaction();

            $this->getDb()->query($enqueueStatement,
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