<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 23.10.2017
 * Time: 17:59
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueryService;


use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @inheritdoc
 */
class DefaultQueryService implements QueryServiceInterface {

    /**
     * @var string
     */
    private $segmentAssignmentIndexTable = '';

    /**
     * @var TypeMapperInterface
     */
    private $typeMapper = null;


    public function __construct(string $segmentAssignmentIndexTable, TypeMapperInterface $typeMapper) {
        $this->setSegmentAssignmentIndexTable($segmentAssignmentIndexTable);
        $this->setTypeMapper($typeMapper);
    }

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
     * @inheritDoc
     */
    public function bySegmentIds(AbstractListing $listing, array $segmentIds, $concatMode = self::MODE_DISJUNCTION) {
        if([] === $segmentIds){
            throw new \LogicException('array $segmentIds must not be empty');
        }

        $elementType = $this->getTypeMapper()->getTypeStringByListing($listing);
        $idColumn = TypeMapperInterface::TYPE_OBJECT === $elementType ? '`o_id`' : '`id`';

        $existsStatements = array_map(function(string $segmentId) use($elementType, $idColumn){
            return "(EXISTS( SELECT `elementId` FROM {$this->getSegmentAssignmentIndexTable()} WHERE `elementId` = $idColumn AND `elementType` = '$elementType' AND `segmentId` = $segmentId ))";
        }, $segmentIds);

        $listing->addConditionParam(join(" $concatMode ", $existsStatements));
    }
}