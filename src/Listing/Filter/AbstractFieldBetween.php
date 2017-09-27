<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use Pimcore\Db;
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

    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param Db\ZendCompatibility\QueryBuilder $query
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, Db\ZendCompatibility\QueryBuilder $query)
    {
        $from = $this->getFromValue();
        $to = $this->getToValue();

        if (null === $from && null === $to) {
            return;
        }

        $tableName = $this->getTableName($listing->getClassId());
        $subSelect = Db::getConnection()->select();

        if (null !== $from) {
            $operator = $this->getOperator(static::TYPE_FROM);
            $subSelect->where(sprintf('`%s`.`%s` %s ?', $tableName, $this->field, $operator), $from);
        }

        if (null !== $to) {
            $operator = $this->getOperator(static::TYPE_TO);
            $subSelect->where(sprintf('`%s`.`%s` %s ?', $tableName, $this->field, $operator), $to);
        }

        $query->where(implode(' ', $subSelect->getPart(Db\ZendCompatibility\QueryBuilder::WHERE)));
    }
}
