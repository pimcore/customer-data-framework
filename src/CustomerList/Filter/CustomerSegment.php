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

namespace CustomerManagementFrameworkBundle\CustomerList\Filter;

use CustomerManagementFrameworkBundle\Listing\Filter\AbstractFilter;
use CustomerManagementFrameworkBundle\Listing\Filter\OnCreateQueryFilterInterface;
use CustomerManagementFrameworkBundle\Service\MariaDb;
use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Listing as CoreListing;

class CustomerSegment extends AbstractFilter implements OnCreateQueryFilterInterface
{
    /**
     * Counter to build distinct identifiers across different segment filters
     *
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
    protected $type = self::OPERATOR_OR;

    /**
     * @var DataObject\CustomerSegmentGroup
     */
    protected $segmentGroup;

    /**
     * @var DataObject\CustomerSegment[]
     */
    protected $segments = [];

    /**
     * Relations to operate on
     *
     * @var array
     */
    protected $relationNames = [
        'manualSegments',
        'calculatedSegments',
    ];

    /**
     * @param DataObject\CustomerSegment[] $segments
     * @param DataObject\CustomerSegmentGroup|null $segmentGroup
     * @param string $type
     */
    public function __construct(array $segments, DataObject\CustomerSegmentGroup $segmentGroup = null, $type = self::OPERATOR_AND)
    {
        $this->identifier = $this->buildIdentifier($segmentGroup);
        $this->segmentGroup = $segmentGroup;
        $this->type = $type;

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
     *
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
     * @param DataObject\CustomerSegmentGroup $segmentGroup
     *
     * @return string
     */
    protected function buildIdentifier(DataObject\CustomerSegmentGroup $segmentGroup = null)
    {
        return sprintf(
            'fltr_seg_%d_%d',
            $segmentGroup ? $segmentGroup->getId() : 'default',
            static::$index++
        );
    }

    /**
     * @param DataObject\CustomerSegment $segment
     *
     * @return $this
     */
    protected function addCustomerSegment(DataObject\CustomerSegment $segment)
    {
        if ($segment->getGroup() && null !== $this->segmentGroup) {
            if ($segment->getGroup()->getId() !== $this->segmentGroup->getId()) {
                throw new \InvalidArgumentException('Segment does not belong to the defined segment group');
            }
        }

        $this->segments[$segment->getId()] = $segment;

        return $this;
    }

    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        if (count($this->segments) === 0) {
            return;
        }

        if ($this->type === self::OPERATOR_OR) {
            $this->applyOrQuery($listing, $queryBuilder);
        } else {
            $this->applyAndQuery($listing, $queryBuilder);
        }
    }

    /**
     * Add a single join with a IN() conditions. If any of the segment IDs matches, the row will be returned
     *
     * @param CoreListing\Concrete $listing
     * @param QueryBuilder $queryBuilder
     */
    protected function applyOrQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        $segmentIds = array_map(
            function (DataObject\CustomerSegment $segment) {
                return $segment->getId();
            },
            $this->segments
        );

        $joinName = sprintf(
            '%s_%s',
            $this->identifier,
            strtolower($this->type)[0]
        );

        $this->addJoin($listing, $queryBuilder, $joinName, $segmentIds);
    }

    /**
     * Add one join per ID we want to search. If any of the joins does not match, the query will fail
     *
     * @param CoreListing\Concrete $listing
     * @param QueryBuilder $queryBuilder
     */
    protected function applyAndQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        $index = 0;
        foreach ($this->segments as $segment) {
            $joinName = sprintf(
                '%s_%s_%d',
                $this->identifier,
                strtolower($this->type)[0],
                $index++
            );

            $this->addJoin($listing, $queryBuilder, $joinName, $segment->getId());
        }
    }

    /**
     * Add the actual INNER JOIN acting as filter
     *
     * @param CoreListing\Concrete $listing
     * @param QueryBuilder $queryBuilder
     * @param string $joinName
     * @param int|array $conditionValue
     */
    protected function addJoin(
        CoreListing\Concrete $listing,
        QueryBuilder $queryBuilder,
        $joinName,
        $conditionValue
    ) {
        $tableName = $listing->getDao()->getTableName();
        $relationsTableName = $this->getTableName($listing->getClassId(), 'object_relations_');

        if (is_array($conditionValue)) {
            $conditionValue = MariaDb::quoteArray($conditionValue);
        }

        $relationNames = implode(',', MariaDb::quoteArray($this->relationNames));

        // relation matches one of our field names and relates to our current object
        $idField = DataObject\Service::getVersionDependentDatabaseColumnName('id');
        $baseCondition = sprintf(
            '`%1$s`.fieldname IN (%2$s) AND `%1$s`.src_id = ' . "`$tableName`." . $idField,
            $joinName,
            $relationNames
        );

        $condition = $baseCondition;

        if ($this->type === self::OPERATOR_OR) {
            // must match any of the passed IDs
            $condition .= sprintf(
                ' AND %1$s.dest_id IN (%2$s)',
                $joinName,
                implode(',', $conditionValue)
            );
        } else {
            // runs an extra join for every ID - all joins must match
            $condition .= sprintf(
                ' AND %1$s.dest_id = %2$s',
                $joinName,
                $conditionValue
            );
        }

        $queryBuilder->join(
            $tableName,
            $relationsTableName,
            $joinName,
            $condition
        );
    }
}
