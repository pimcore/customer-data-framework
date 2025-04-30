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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\SegmentAssigner;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use Doctrine\DBAL\Connection;
use Pimcore\Logger;
use Pimcore\Model\Element\ElementInterface;

class SegmentAssigner implements SegmentAssignerInterface
{
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
     * @var string
     */
    private $segmentAssignmentIndexTable = '';

    public function __construct(string $segmentAssignmentTable, string $segmentAssignmentQueueTable, string $segmentAssignmentIndexTable, Connection $db, TypeMapperInterface $typeMapper)
    {
        $this->setSegmentAssignmentTable($segmentAssignmentTable);
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setDb($db);
        $this->setTypeMapper($typeMapper);
    }

    public function getDb(): Connection
    {
        return $this->db;
    }

    public function setDb(Connection $db)
    {
        $this->db = $db;
    }

    public function getTypeMapper(): TypeMapperInterface
    {
        return $this->typeMapper;
    }

    public function setTypeMapper(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    public function getSegmentAssignmentTable(): string
    {
        return $this->segmentAssignmentTable;
    }

    public function setSegmentAssignmentTable(string $segmentAssignmentTable)
    {
        $this->segmentAssignmentTable = $segmentAssignmentTable;
    }

    public function getSegmentAssignmentQueueTable(): string
    {
        return $this->segmentAssignmentQueueTable;
    }

    public function setSegmentAssignmentQueueTable(string $segmentAssignmentQueueTable)
    {
        $this->segmentAssignmentQueueTable = $segmentAssignmentQueueTable;
    }

    public function getSegmentAssignmentIndexTable(): string
    {
        return $this->segmentAssignmentIndexTable;
    }

    public function setSegmentAssignmentIndexTable(string $segmentAssignmentIndexTable)
    {
        $this->segmentAssignmentIndexTable = $segmentAssignmentIndexTable;
    }

    public function assign(ElementInterface $element, bool $breaksInheritance, array $segments): bool
    {
        $segmentIds = array_map(static function ($segment) {
            return $segment instanceof CustomerSegmentInterface ? (string) $segment->getId() : $segment;
        }, $segments);

        return $this->assignById((string) $element->getId(), $this->getTypeMapper()->getTypeStringByObject($element), $breaksInheritance, $segmentIds);
    }

    public function assignById(string $elementId, string $type, bool $breaksInheritance, array $segmentIds): bool
    {
        try {
            $statement = "INSERT INTO `{$this->getSegmentAssignmentTable()}` (`elementId`, `elementType`, `segments`, `breaksInheritance`, `inPreparation`) " .
                'VALUES (:elementId, :elementType, :segmentIds, :breaksInheritance, 1) ' .
                'ON DUPLICATE KEY UPDATE `segments` = :segmentIds, `breaksInheritance` = :breaksInheritance, `inPreparation` = 1;';

            $this->db->beginTransaction();

            $this->db->executeQuery($statement, [
                'elementId' => $elementId,
                'elementType' => $type,
                'segmentIds' => join(',', $segmentIds),
                'breaksInheritance' => (int)$breaksInheritance,
            ]);

            $this->db->commit();

            return true;
        } catch (\Throwable $exception) {
            Logger::error($exception->getMessage());

            return false;
        }
    }

    public function removeElementById(string $elementId, string $type): bool
    {
        $db = $this->getDb();
        $tActive = $db->isTransactionActive();

        try {
            $deletePattern = 'DELETE FROM %s WHERE `elementId` = :elementId AND `elementType` = :elementType; ';
            $tables = [
                $this->getSegmentAssignmentTable(),
                $this->getSegmentAssignmentQueueTable(),
                $this->getSegmentAssignmentIndexTable(),
            ];

            if (!$tActive) {
                // start a new transaction
                $db->beginTransaction();
            }

            foreach ($tables as $table) {
                $statement = sprintf($deletePattern, $table);

                $this->getDb()->executeQuery($statement,
                    [
                        'elementId' => $elementId,
                        'elementType' => $type,
                    ]
                );
            }

            if (!$tActive) {
                $db->commit();
            }

            return true;
        } catch (\Throwable $exception) {
            if (!$tActive) {
                $db->rollBack();
            }
            Logger::error($exception->getMessage());

            return false;
        }
    }
}
