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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\Indexer;

use CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder\QueueBuilderInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions\StoredFunctionsInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db\Connection;
use Pimcore\Logger;

class Indexer implements IndexerInterface
{
    use LoggerAware;

    const PAGE_SIZE = 200;

    /**
     * @var string
     */
    private $segmentAssignmentTable = '';

    /**
     * @var string
     */
    private $segmentAssignmentIndexTable = '';

    /**
     * @var string
     */
    private $segmentAssignmentQueueTable = '';

    /**
     * @var StoredFunctionsInterface
     */
    private $storedFunctions = null;

    /**
     * @var QueueBuilderInterface
     */
    private $queueBuilder = null;

    /**
     * @var Connection
     */
    private $db = null;

    /**
     * @param string $segmentAssignmentTable
     * @param string $segmentAssignmentIndexTable
     * @param string $segmentAssignmentQueueTable
     * @param StoredFunctionsInterface $storedFunctions
     * @param QueueBuilderInterface $queueBuilder
     * @param Connection $db
     */
    public function __construct(string $segmentAssignmentTable, string $segmentAssignmentIndexTable, string $segmentAssignmentQueueTable, StoredFunctionsInterface $storedFunctions, QueueBuilderInterface $queueBuilder, Connection $db)
    {
        $this->setSegmentAssignmentTable($segmentAssignmentTable);
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setStoredFunctions($storedFunctions);
        $this->setQueueBuilder($queueBuilder);
        $this->setDb($db);
    }

    /**
     * @return string
     */
    public function getSegmentAssignmentTable(): string {
        return $this->segmentAssignmentTable;
    }

    /**
     * @param string $segmentAssignmentTable
     */
    public function setSegmentAssignmentTable(string $segmentAssignmentTable) {
        $this->segmentAssignmentTable = $segmentAssignmentTable;
    }

    /**
     * @return string
     */
    public function getSegmentAssignmentIndexTable(): string
    {
        return $this->segmentAssignmentIndexTable;
    }

    /**
     * @param string $segmentAssignmentIndexTable
     */
    public function setSegmentAssignmentIndexTable(string $segmentAssignmentIndexTable)
    {
        $this->segmentAssignmentIndexTable = $segmentAssignmentIndexTable;
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
     * @return StoredFunctionsInterface
     */
    public function getStoredFunctions(): StoredFunctionsInterface {
        return $this->storedFunctions;
    }

    /**
     * @param StoredFunctionsInterface $storedFunctions
     */
    public function setStoredFunctions(StoredFunctionsInterface $storedFunctions) {
        $this->storedFunctions = $storedFunctions;
    }

    /**
     * @return QueueBuilderInterface
     */
    public function getQueueBuilder(): QueueBuilderInterface {
        return $this->queueBuilder;
    }

    /**
     * @param QueueBuilderInterface $queueBuilder
     */
    public function setQueueBuilder(QueueBuilderInterface $queueBuilder) {
        $this->queueBuilder = $queueBuilder;
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @param Connection $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function processQueue(): bool
    {
        $this->buildQueue(); // first enrich the queue table with all elements that are `inPreparation` and their children

        $chunkStatement = sprintf('SELECT * FROM `%s` LIMIT %s', $this->getSegmentAssignmentQueueTable(), static::PAGE_SIZE);
        $round = 0;
        $queuedElements = $this->getDb()->fetchAll($chunkStatement);

        while (sizeof($queuedElements) > 0) {
            foreach ($queuedElements as $element) {
                $this->processElement($element);
            }

            $queuedElements = $this->getDb()->fetchAll($chunkStatement);
            \Pimcore::collectGarbage();

            $this->getLogger()->info('### round: ' . ++$round);
        }

        return true;
    }

    /**
     * processes a single element,
     * inserts one row for each segment assigned to that element
     * and finally dequeues the element
     *
     * @param array $element
     */
    private function processElement(array $element)
    {
        $elementId = $element['elementId'];
        $elementType = $element['elementType'];

        $segmentIds = $this->getStoredFunctions()->retrieve($elementId, $elementType);


        $values = join(',', array_map(function ($segmentId) use ($elementId, $elementType) {
            $segmentId = '' !== $segmentId ? $segmentId : 0; //filter empty string when nothing is assigned
            return sprintf('(%s, "%s", %s)', $elementId, $elementType, $segmentId);
        }, $segmentIds));

        $formatArguments = [
            1 => $this->getSegmentAssignmentIndexTable(),
            2 => $this->getSegmentAssignmentQueueTable(),
            3 => $this->getSegmentAssignmentTable(),
            4 => $values
        ];

        $statement = vsprintf(
            'INSERT INTO `%1$s` VALUES %4$s ON DUPLICATE KEY UPDATE `elementId` = `elementId`;'.
            'DELETE FROM `%1$s` WHERE `elementId` = :elementId AND `elementType` = :elementType AND FIND_IN_SET(`segmentId`, :segmentIds) = 0;'.
            'DELETE FROM `%2$s` WHERE `elementId` = :elementId AND `elementType` = :elementType;'.
            'UPDATE %3$s SET `inPreparation` = 0 WHERE `elementId` = :elementId AND `elementType` = :elementType;',
            $formatArguments);

        $this->getDb()->beginTransaction();

        $this->getDb()->executeQuery($statement,
            [
                'elementId' => $elementId,
                'elementType' => $elementType,
                'segmentIds' => join(',', $segmentIds)
            ]);

        try {
            $this->getDb()->commit();
        } catch (\Throwable $exception) {
            $this->getLogger()->error($exception->getMessage());
        }
    }

    /**
     * Enqueues all elements with the flag `inPreparation` set to 1 and all of their children
     *
     * This is done so elements do not have to be enqueued during the saving process in the pimcore backend
     */
    private function buildQueue() {
        $parentElements = $this->getDb()->fetchAll("SELECT * FROM `{$this->getSegmentAssignmentTable()}` WHERE `inPreparation` = 1");

        foreach ($parentElements as $element) {
            $id = $element['elementId'] ?? '';
            $type = $element['elementType'] ?? '';

            $this->getQueueBuilder()->enqueue($id, $type);
            $this->getQueueBuilder()->enqueueChildren($id, $type);
        }
    }
}
