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

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Db;
use Pimcore\Model\DataObject\Listing as CoreListing;

abstract class AbstractFieldValue extends AbstractFilter implements OnCreateQueryFilterInterface
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $inverse = false;

    /**
     * @param string|array $fields
     * @param string $value
     * @param bool $inverse
     */
    public function __construct($fields, $value, $inverse = false)
    {
        if (is_array($fields)) {
            $this->fields = $fields;
        } else {
            if (!empty($fields)) {
                $this->fields[] = $fields;
            }
        }

        if (empty($this->fields)) {
            throw new \InvalidArgumentException('Field filter needs at least one field to operate on');
        }

        $this->value = trim($value);
        $this->inverse = (bool)$inverse;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function processValue($value)
    {
        return $value;
    }

    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        if (empty($this->value)) {
            return;
        }

        $value = $this->processValue($this->value);

        // we just have one field so match -> no sub-query needed
        if (count($this->fields) === 1) {
            $this->applyFieldCondition($this->fields[0], $value, $listing, $queryBuilder);
        } else {
            // build a sub-query to assemble where condition
            $subQuery = Db::get()->createQueryBuilder();
            $operator = $this->getBooleanFieldOperator();

            foreach ($this->fields as $field) {
                $this->applyFieldCondition($field, $value, $listing, $subQuery, $operator);
            }

            // add assembled sub-query where condition to our main query
            $queryBuilder->andWhere(implode(' ', $subQuery->getQueryPart('where')));
        }
    }

    /**
     * Apply field condition to query/sub-query
     *
     * @param string $field
     * @param mixed $value
     * @param CoreListing\Concrete $listing
     * @param QueryBuilder $queryBuilder
     * @param string $operator
     */
    protected function applyFieldCondition(
        $field,
        $value,
        CoreListing\Concrete $listing,
        QueryBuilder $queryBuilder,
        $operator = self::OPERATOR_AND
    ) {
        $tableName = $this->getTableName($listing->getClassId());

        $condition = sprintf(
            '`%s`.`%s` %s %s',
            $tableName,
            $field,
            $this->getComparisonOperator(),
            $listing->quote($value)
        );

        if ($operator === self::OPERATOR_OR) {
            $queryBuilder->orWhere($condition); //->setParameter($parameterName);
        } else {
            $queryBuilder->andWhere($condition); //->setParameter($parameterName);
        }
    }

    /**
     * Get operator to join field conditions on. Uses AND for inverse searches, OR for normal ones.
     *
     * @return string
     */
    protected function getBooleanFieldOperator()
    {
        if ($this->inverse) {
            return self::OPERATOR_AND;
        } else {
            return self::OPERATOR_OR;
        }
    }

    /**
     * @return string
     */
    abstract protected function getComparisonOperator();
}
