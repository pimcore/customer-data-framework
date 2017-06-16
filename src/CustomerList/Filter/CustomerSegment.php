<?php

namespace CustomerManagementFrameworkBundle\CustomerList\Filter;

use BackendToolkit\Listing\Filter\AbstractFilter;
use BackendToolkit\Listing\OnCreateQueryFilterInterface;
use Pimcore\Db;
use Pimcore\Model\Object;
use Pimcore\Model\Object\Listing as CoreListing;

class CustomerSegment extends AbstractFilter implements OnCreateQueryFilterInterface
{
    /**
     * Counter to build distinct identifiers across different segment filters
     * @var int
     */
    protected static $index = 0;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $type = \Zend_Db_Select::SQL_AND;

    /**
     * @var Object\CustomerSegmentGroup
     */
    protected $segmentGroup;

    /**
     * @var Object\CustomerSegment[]
     */
    protected $segments = [];

    /**
     * Relations to operate on
     * @var array
     */
    protected $relationNames = [
        'manualSegments',
        'calculatedSegments'
    ];

    /**
     * @param Object\CustomerSegment[] $segments
     * @param Object\CustomerSegmentGroup|null $segmentGroup
     * @param string $type
     */
    public function __construct(array $segments, Object\CustomerSegmentGroup $segmentGroup = null, $type = \Zend_Db_Select::SQL_AND)
    {
        $this->identifier   = $this->buildIdentifier($segmentGroup);
        $this->segmentGroup = $segmentGroup;
        $this->type         = $type;

        foreach ($segments as $segment) {
            $this->addCustomerSegment($segment);
        }
    }

    /**
     * @return array
     */
    public function getRelationNames()
    {
        return $this->relationNames;
    }

    /**
     * @param array $relationNames
     * @return $this
     */
    public function setRelationNames(array $relationNames)
    {
        $this->relationNames = $relationNames;

        return $this;
    }

    /**
     * Build an unique identifier for this filter which acts as join name. Needed in case multiple segment filters for the
     * same segment group are applied.
     *
     * @param Object\CustomerSegmentGroup $segmentGroup
     * @return string
     */
    protected function buildIdentifier(Object\CustomerSegmentGroup $segmentGroup = null)
    {
        return sprintf(
            'fltr_seg_%d_%d',
            $segmentGroup ? $segmentGroup->getId() : 'default',
            static::$index++
        );
    }

    /**
     * @param Object\CustomerSegment $segment
     * @return $this
     */
    protected function addCustomerSegment(Object\CustomerSegment $segment)
    {
        if ($segment->getGroup() && null !== $this->segmentGroup) {
            if ($segment->getGroup()->getId() !== $this->segmentGroup->getId()) {
                throw new \InvalidArgumentException('Segment does not belong to the defined segment group');
            }
        }

        $this->segments[$segment->getId()] = $segment;

        return $this;
    }

    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param \Zend_Db_Select $query
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, \Zend_Db_Select $query)
    {
        if (count($this->segments) === 0) {
            return;
        }

        if ($this->type === \Zend_Db_Select::SQL_OR) {
            $this->applyOrQuery($listing, $query);
        } else {
            $this->applyAndQuery($listing, $query);
        }
    }

    /**
     * Add a single join with a IN() conditions. If any of the segment IDs matches, the row will be returned
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param \Zend_Db_Select $query
     */
    protected function applyOrQuery(CoreListing\Concrete $listing, \Zend_Db_Select $query)
    {
        $segmentIds = array_map(function (Object\CustomerSegment $segment) {
            return $segment->getId();
        }, $this->segments);

        $joinName = sprintf(
            '%s_%s',
            $this->identifier,
            strtolower($this->type)[0]
        );

        $this->addJoin($listing, $query, $joinName, $segmentIds);
    }

    /**
     * Add one join per ID we want to search. If any of the joins does not match, the query will fail
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param \Zend_Db_Select $query
     */
    protected function applyAndQuery(CoreListing\Concrete $listing, \Zend_Db_Select $query)
    {
        $index = 0;
        foreach ($this->segments as $segment) {
            $joinName = sprintf(
                '%s_%s_%d',
                $this->identifier,
                strtolower($this->type)[0],
                $index++
            );

            $this->addJoin($listing, $query, $joinName, $segment->getId());
        }
    }

    /**
     * Add the actual INNER JOIN acting as filter
     *
     * @param CoreListing\Concrete $listing
     * @param \Zend_Db_Select $query
     * @param string $joinName
     * @param int|array $conditionValue
     */
    protected function addJoin(CoreListing\Concrete $listing, \Zend_Db_Select $query, $joinName, $conditionValue)
    {
        $tableName          = $this->getTableName($listing->getClassId());
        $relationsTableName = $this->getTableName($listing->getClassId(), 'object_relations_');

        $connection = Db::get();

        // relation matches one of our field names and relates to our current object
        $baseCondition = $connection->quoteInto(sprintf(
            '%1$s.fieldname IN (?) AND %1$s.src_id = %2$s.o_id',
            $joinName,
            $tableName
        ), $this->relationNames);

        $condition = $baseCondition;
        if ($this->type === \Zend_Db_Select::SQL_OR) {
            // must match any of the passed IDs
            $condition .= $connection->quoteInto(sprintf(
                ' AND %s.dest_id IN (?)',
                $joinName
            ), $conditionValue);
        } else {
            // runs an extra join for every ID - all joins must match
            $condition .= $connection->quoteInto(sprintf(
                ' AND %s.dest_id = ?',
                $joinName
            ), $conditionValue);
        }

        // add field names and condition value to condition
        $condition = Db::get()->quoteInto(
            $condition,
            [
                $this->relationNames,
                $conditionValue
            ]
        );

        $query->join(
            [$joinName => $relationsTableName],
            $condition,
            ''
        );
    }
}
