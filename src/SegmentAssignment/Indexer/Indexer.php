<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-12
 * Time: 4:14 PM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\Indexer;


use \Pimcore\Db\Connection;

class Indexer implements IndexerInterface {

    const STORED_FUNCTIONS = [
        'document' => 'PLUGIN_CMF_COLLECT_DOCUMENT_SEGMENT_ASSIGNMENTS',
        'asset' => 'PLUGIN_CMF_COLLECT_ASSET_SEGMENT_ASSIGNMENTS',
        'object' => 'PLUGIN_CMF_COLLECT_OBJECT_SEGMENT_ASSIGNMENTS'
    ];

    const PAGE_SIZE = 200;

    /**
     * @var string
     */
    private $segmentAssignmentIndexTable = '';

    /**
     * @var string
     */
    private $segmentAssignmentQueueTable = '';

    /**
     * @var Connection
     */
    private $db = null;

    /**
     * @return string
     */
    public function getSegmentAssignmentIndexTable(): string {
        return $this->segmentAssignmentIndexTable;
    }

    /**
     * @param string $segmentAssignmentIndexTable
     */
    public function setSegmentAssignmentIndexTable(string $segmentAssignmentIndexTable) {
        $this->segmentAssignmentIndexTable = $segmentAssignmentIndexTable;
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
    public function getDb() {
        return $this->db;
    }

    /**
     * @param Connection $db
     */
    public function setDb($db) {
        $this->db = $db;
    }

    /**
     * @param string $segmentAssignmentIndexTable
     * @param string $segmentAssignmentQueueTable
     * @param Connection $db
     */
    public function __construct(string $segmentAssignmentIndexTable, string $segmentAssignmentQueueTable, Connection $db) {
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setDb($db);
    }

    /**
     * @inheritDoc
     */
    public function processQueue(): bool {
        $chunkStatement = sprintf('SELECT * FROM `%s` LIMIT %s', $this->getSegmentAssignmentQueueTable(), static::PAGE_SIZE);

        $queuedElements = $this->getDb()->fetchAll($chunkStatement);
var_dump($queuedElements);
        while(sizeof($queuedElements) > 0) {
            foreach($queuedElements as $element) {
                $this->processElement($element);
            }

            $queuedElements = $this->getDb()->fetchAll($chunkStatement);
            \Pimcore::collectGarbage();
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
    private function processElement(array $element) {
        $elementId = $element['elementId'];
        $elementType = $element['elementType'];

        $segmentIds = explode(',', $this->getDb()->fetchColumn(sprintf('SELECT %s(%s)', static::STORED_FUNCTIONS[$elementType], $elementId)));

        if(1 === sizeof($segmentIds) && "" === $segmentIds[0]) {
            $segmentIds[0] = 0;
        }

        $values = join(',', array_map(function($segmentId) use ($elementId, $elementType) {
            return sprintf('(%s, "%s", %s)', $elementId, $elementType, $segmentId);
        }, $segmentIds));
echo $values;
        $formatArguments = [
            1 => $this->getSegmentAssignmentIndexTable(),
            2 => $this->getSegmentAssignmentQueueTable(),
            3 => $elementId,
            4 => $elementType,
            5 => $values,
            6 => join(',', $segmentIds)
        ];

        $this->getDb()->query(vsprintf('START TRANSACTION;'.
        'INSERT INTO `%1$s` VALUES %5$s ON DUPLICATE KEY UPDATE `elementId` = `elementId`;'.
        'DELETE FROM `%1$s` WHERE `elementId` = %3$s AND `elementType` = "%4$s" AND FIND_IN_SET(`segmentId`, "%6$s") = 0;'.
        'DELETE FROM `%2$s` WHERE `elementId` = "%3$s" AND `elementType` = "%4$s";'.
        'COMMIT;', $formatArguments));
    }
}
