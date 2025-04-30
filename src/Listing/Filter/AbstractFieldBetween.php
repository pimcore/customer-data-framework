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

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Model\DataObject\Listing as CoreListing;

abstract class AbstractFieldBetween extends AbstractFilter implements OnCreateQueryFilterInterface
{
    const TYPE_FROM = 'from';

    const TYPE_TO = 'to';

    /**
     * @var string
     */
    protected $field;

    /**
     * @var bool
     */
    protected $inclusive = true;

    /**
     * @param string $field
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * @param bool $inclusive
     *
     * @return $this
     */
    public function setInclusive($inclusive)
    {
        $this->inclusive = (bool)$inclusive;

        return $this;
    }

    /**
     * @return bool
     */
    public function isInclusive()
    {
        return $this->inclusive;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getOperator($type = self::TYPE_FROM)
    {
        $operator = null;
        if ($type === static::TYPE_FROM) {
            $operator = '>';
        } else {
            if ($type === static::TYPE_TO) {
                $operator = '<';
            }
        }

        if (null === $operator) {
            throw new \RuntimeException('Invalid operator type');
        }

        if ($this->isInclusive()) {
            $operator = $operator.'=';
        }

        return $operator;
    }

    /**
     * @return mixed
     */
    abstract protected function getFromValue();

    /**
     * @return mixed
     */
    abstract protected function getToValue();

    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        $from = $this->getFromValue();
        $to = $this->getToValue();

        if (null === $from && null === $to) {
            return;
        }

        $tableName = $this->getTableName($listing->getClassId());
        $whereCondition = [];

        if (null !== $from) {
            $operator = $this->getOperator(static::TYPE_FROM);
            $whereCondition[] = sprintf('`%s`.`%s` %s %s', $tableName, $this->field, $operator, $listing->quote((string)$from));
        }

        if (null !== $to) {
            $operator = $this->getOperator(static::TYPE_TO);
            $whereCondition[] = sprintf('`%s`.`%s` %s %s', $tableName, $this->field, $operator, $listing->quote((string)$to));
        }
        $whereSql = implode(' AND ', $whereCondition);

        $queryBuilder->andWhere($whereSql);
    }
}
