<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-20
 * Time: 9:12 AM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\SegmentAssigner;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use Pimcore\Db\Connection;
use Pimcore\Model\Element\ElementInterface;
use function Sabre\Event\Loop\instance;

class SegmentAssigner implements SegmentAssignerInterface {

    /**
     * @var Connection
     */
    private $db = null;

    /**
     * @var TypeMapperInterface
     */
    private $typeMapper = null;

    /**
     * @var string
     */
    private $segmentAssignmentTable = '';

    /**
     * @var string
     */
    private $segmentAssignmentQueueTable = '';

    /**
     * @inheritDoc
     */
    public function __construct(string $segmentAssignmentTable, string $segmentAssignmentQueueTable, Connection $db, TypeMapperInterface $typeMapper) {
        $this->setSegmentAssignmentTable($segmentAssignmentTable);
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setDb($db);
        $this->setTypeMapper($typeMapper);
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
     * @return TypeMapperInterface
     */
    public function getTypeMapper(): TypeMapperInterface {
        return $this->typeMapper;
    }

    /**
     * @param TypeMapperInterface $typeMapper
     */
    public function setTypeMapper(TypeMapperInterface $typeMapper) {
        $this->typeMapper = $typeMapper;
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
     * @inheritDoc
     */
    public function assign(ElementInterface $element, bool $breaksInheritance, array $segments): bool {
        $segmentIds = array_map(function ($segment) {
            return $segment instanceof CustomerSegmentInterface ? $segment->getId() : $segment;
        }, $segments);

        return $this->assignById($element->getId(), $this->getTypeMapper()->getTypeStringByObject($element), $breaksInheritance, $segmentIds);
    }

    /**
     * @inheritDoc
     */
    public function assignById(string $elementId, string $type, bool $breaksInheritance, array $segmentIds): bool {
        try {
            $formatArguments = [
                1 => $this->getSegmentAssignmentTable(),
                2 => $this->getSegmentAssignmentQueueTable(),
                3 => $elementId,
                4 => $type,
                5 => (int) $breaksInheritance,
                6 => join(',', $segmentIds)];

            $statement = vsprintf(
                'START TRANSACTION;'.
                'INSERT INTO `%1$s` VALUES (%3$s, "%4$s", %5$s, "%6$s") ON DUPLICATE KEY UPDATE `breaksInheritance` = %5$s, `segments` = "%6$s";'.
                'INSERT INTO `%2$s` VALUES (%3$s, "%4$s") ON DUPLICATE KEY UPDATE `elementId` = `elementId`;'.
                'COMMIT;', $formatArguments);

            $this->getDb()->query($statement);
            $this->enqueueChildren($elementId, $type);

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function enqueueChildren(string $elementId, string $type): bool {
        try {
            $formatArguments = [
                1 => $this->getSegmentAssignmentQueueTable(),
                2 => $type == 'object' ? 'o_id' : 'id',
                3 => $type,
                4 => $type . 's',
                5 => $type == 'object' ? 'o_path' : 'path',
                6 => $type == 'object' ? 'o_key' : 'key',
                7 => $elementId
            ];

            $enqueueStatement = vsprintf('INSERT INTO `%1$s` (`elementId`, `elementType`) ' .
                'SELECT `%2$s` AS elementId, "%3$s" AS elementType FROM `%4$s` ' .
                'WHERE `%5$s` LIKE CONCAT( ' .
                '(SELECT CONCAT(`%5$s`, `%6$s`) FROM `%4$s` WHERE `%2$s` = "%7$s")' .
                ', "%%") ON DUPLICATE KEY UPDATE `elementId` = `elementId`', $formatArguments);

            $this->getDb()->query($enqueueStatement);
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}