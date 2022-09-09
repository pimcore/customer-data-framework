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

    /**
     * @inheritDoc
     */
    public function __construct(string $segmentAssignmentTable, string $segmentAssignmentQueueTable, string $segmentAssignmentIndexTable, Connection $db, TypeMapperInterface $typeMapper)
    {
        $this->setSegmentAssignmentTable($segmentAssignmentTable);
        $this->setSegmentAssignmentQueueTable($segmentAssignmentQueueTable);
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setDb($db);
        $this->setTypeMapper($typeMapper);
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
     * @return TypeMapperInterface
     */
    public function getTypeMapper(): TypeMapperInterface
    {
        return $this->typeMapper;
    }

    /**
     * @param TypeMapperInterface $typeMapper
     */
    public function setTypeMapper(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    /**
     * @return string
     */
    public function getSegmentAssignmentTable(): string
    {
        return $this->segmentAssignmentTable;
    }

    /**
     * @param string $segmentAssignmentTable
     */
    public function setSegmentAssignmentTable(string $segmentAssignmentTable)
    {
        $this->segmentAssignmentTable = $segmentAssignmentTable;
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
     * @inheritDoc
     */
    public function assign(ElementInterface $element, bool $breaksInheritance, array $segments): bool
    {
        $segmentIds = array_map(static function ($segment) {
            return $segment instanceof CustomerSegmentInterface ? (string) $segment->getId() : $segment;
        }, $segments);

        return $this->assignById((string) $element->getId(), $this->getTypeMapper()->getTypeStringByObject($element), $breaksInheritance, $segmentIds);
    }

    /**
     * @inheritDoc
     */
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
                'breaksInheritance' => (int)$breaksInheritance
            ]);

            $this->db->commit();

            return true;
        } catch (\Throwable $exception) {
            Logger::error($exception->getMessage());

            return false;
        }
    }

    /**
     * @inheritdoc
     */
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
                        'elementType' => $type
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
