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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueryService;

use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @inheritdoc
 */
class DefaultQueryService implements QueryServiceInterface
{
    /**
     * @var string
     */
    private $segmentAssignmentIndexTable = '';

    /**
     * @var TypeMapperInterface
     */
    private $typeMapper = null;

    public function __construct(string $segmentAssignmentIndexTable, TypeMapperInterface $typeMapper)
    {
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setTypeMapper($typeMapper);
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
     * @inheritDoc
     */
    public function bySegmentIds(AbstractListing $listing, array $segmentIds, $concatMode = self::MODE_DISJUNCTION)
    {
        if ([] === $segmentIds) {
            throw new \LogicException('array $segmentIds must not be empty');
        }

        $elementType = $this->getTypeMapper()->getTypeStringByListing($listing);
        $idColumn = sprintf('`%s`', Service::getVersionDependentDatabaseColumnName('id'))  ;

        $existsStatements = array_map(function (string $segmentId) use ($elementType, $idColumn) {
            return "(EXISTS( SELECT `elementId` FROM {$this->getSegmentAssignmentIndexTable()} WHERE `elementId` = $idColumn AND `elementType` = '$elementType' AND `segmentId` = $segmentId ))";
        }, $segmentIds);

        $listing->addConditionParam(join(" $concatMode ", $existsStatements));
    }
}
