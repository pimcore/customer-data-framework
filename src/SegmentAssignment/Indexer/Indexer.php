<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\Indexer;

use CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder\QueueBuilderInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions\StoredFunctionsInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Doctrine\DBAL\Connection;
use Pimcore\Db;

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

    public function __construct(string $segmentAssignmentTable, string $segmentAssignmentIndexTable, string $segmentAssignmentQueueTable, StoredFunctionsInterface $storedFunctions, QueueBuilderInterface $queueBuilder)
    {
        $this->setSegmentAssignmentTable($segmentAssignmentTable);
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setStoredFunctions($storedFunctions);
        $this->setQueueBuilder($queueBuilder);
    }

    public function getSegmentAssignmentTable(): string
    {
        return $this->segmentAssignmentTable;
    }

    public function setSegmentAssignmentTable(string $segmentAssignmentTable)
    {
        $this->segmentAssignmentTable = $segmentAssignmentTable;
    }

    public function getSegmentAssignmentIndexTable(): string
    {
        return $this->segmentAssignmentIndexTable;
    }

    public function setSegmentAssignmentIndexTable(string $segmentAssignmentIndexTable)
    {
        $this->segmentAssignmentIndexTable = $segmentAssignmentIndexTable;
    }

    public function getSegmentAssignmentQueueTable(): string
    {
        return $this->segmentAssignmentQueueTable;
    }

    public function setSegmentAssignmentQueueTable(string $segmentAssignmentQueueTable)
    {
        $this->segmentAssignmentQueueTable = $segmentAssignmentQueueTable;
    }

    public function getStoredFunctions(): StoredFunctionsInterface
    {
        return $this->storedFunctions;
    }

    public function setStoredFunctions(StoredFunctionsInterface $storedFunctions)
    {
        $this->storedFunctions = $storedFunctions;
    }

    public function getQueueBuilder(): QueueBuilderInterface
    {
        return $this->queueBuilder;
    }

    public function setQueueBuilder(QueueBuilderInterface $queueBuilder)
    {
        $this->queueBuilder = $queueBuilder;
    }

    /**
     * @return Connection
     */
    public function getDb()
    {
        if ($this->db === null) {
            /** @var Connection $db */
            $db = Db::get();
            $this->db = $db;
        }

        return $this->db;
    }

    /**
     * @param Connection $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    public function processQueue(): bool
    {
        $this->buildQueue(); // first enrich the queue table with all elements that are `inPreparation` and their children

        $chunkStatement = sprintf('SELECT * FROM `%s` LIMIT %s', $this->getSegmentAssignmentQueueTable(), static::PAGE_SIZE);
        $round = 0;
        $queuedElements = $this->getDb()->fetchAllAssociative($chunkStatement);

        while (sizeof($queuedElements) > 0) {
            foreach ($queuedElements as $element) {
                $this->processElement($element);
            }

            $queuedElements = $this->getDb()->fetchAllAssociative($chunkStatement);
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
            4 => $values,
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
                'segmentIds' => join(',', $segmentIds),
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
    private function buildQueue()
    {
        $parentElements = $this->getDb()->fetchAllAssociative("SELECT * FROM `{$this->getSegmentAssignmentTable()}` WHERE `inPreparation` = 1");

        foreach ($parentElements as $element) {
            $id = $element['elementId'] ?? '';
            $type = $element['elementType'] ?? '';

            $this->getQueueBuilder()->enqueue($id, $type);
            $this->getQueueBuilder()->enqueueChildren($id, $type);
        }
    }
}
