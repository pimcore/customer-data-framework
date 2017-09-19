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

    const QUEUE_TABLE = 'plugin_cmf_segment_assignment_queue';
    const INDEX_TABLE = 'plugin_cmf_segment_assignment_index';

    const STORED_FUNCTIONS = [
        'document' => 'PLUGIN_CMF_COLLECT_DOCUMENT_SEGMENT_ASSIGNMENTS',
        'asset' => 'PLUGIN_CMF_COLLECT_ASSET_SEGMENT_ASSIGNMENTS',
        'object' => 'PLUGIN_CMF_COLLECT_OBJECT_SEGMENT_ASSIGNMENTS'
    ];

    const PAGE_SIZE = 200;

    /**
     * @var Connection
     */
    private $db = null;

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
     * Indexer constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db) {
        $this->setDb($db);
    }

    /**
     * @inheritDoc
     */
    public function processQueue(): bool {
        $chunkStatement = sprintf('SELECT * FROM %s LIMIT %s', static::QUEUE_TABLE, static::PAGE_SIZE);

        $queuedElements = $this->getDb()->fetchAll($chunkStatement);

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
     * delete all previously assigned segments and inserts the currently assigned ones
     * inserts one row for each segment assigned to that element
     * and finally dequeues the element
     *
     * @param array $element
     */
    private function processElement(array $element) {
        $elementId = $element['elementId'];
        $elementType = $element['elementType'];

        $segmentIds = explode(',', $this->getDb()->fetchColumn(sprintf('SELECT %s(%s)', static::STORED_FUNCTIONS[$elementType], $elementId)));

        $values = join(',', array_map(function($segmentId) use ($elementId, $elementType) {
            return sprintf('(%s, "%s", %s)', $elementId, $elementType, $segmentId);
        }, $segmentIds));

        $formatArguments = [static::INDEX_TABLE, static::QUEUE_TABLE, $elementId, $elementType, $values];

        $this->getDb()->query(vsprintf('START TRANSACTION;
        DELETE FROM %1$s WHERE `elementId` = "%3$s" AND `elementType` = "%4$s";
        INSERT INTO %1$s VALUES %5$s;
        DELETE FROM %2$s WHERE `elementId` = "%3$s" AND `elementType` = "%4$s";
        COMMIT;
        ', $formatArguments));
    }
}