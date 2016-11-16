<?php

namespace CustomerManagementFramework\Listing\Filter;

use CustomerManagementFramework\Listing\OnCreateQueryFilterInterface;
use Pimcore\Db;
use Pimcore\Model\Object\Listing as CoreListing;

abstract class AbstractFieldBetween extends AbstractField implements OnCreateQueryFilterInterface
{
    const TYPE_FROM = 'from';
    const TYPE_TO = 'to';

    /**
     * @var bool
     */
    protected $inclusive = true;

    /**
     * @param bool $inclusive
     * @return $this
     */
    public function setInclusive($inclusive)
    {
        $this->inclusive = (bool)$inclusive;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isInclusive()
    {
        return $this->inclusive;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function getOperator($type = self::TYPE_FROM)
    {
        $operator = null;
        if ($type === static::TYPE_FROM) {
            $operator = '>';
        } else if ($type === static::TYPE_TO) {
            $operator = '<';
        }

        if (null === $operator) {
            throw new \RuntimeException('Invalid operator type');
        }

        if ($this->isInclusive()) {
            $operator = $operator . '=';
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
     * Apply filter to query
     *
     * @param \Zend_Db_Select $query
     * @param int $classId
     */
    public function applyOnCreateQuery(\Zend_Db_Select $query, $classId)
    {
        $from = $this->getFromValue();
        $to   = $this->getToValue();

        if (null === $from && null === $to) {
            return;
        }

        $tableName = $this->getTableName($classId);
        $subSelect = Db::getConnection()->select();

        if (null !== $from) {
            $operator = $this->getOperator(static::TYPE_FROM);
            $subSelect->where(sprintf('`%s`.`%s` %s ?', $tableName, $this->field, $operator), $from);
        }

        if (null !== $to) {
            $operator = $this->getOperator(static::TYPE_TO);
            $subSelect->where(sprintf('`%s`.`%s` %s ?', $tableName, $this->field, $operator), $to);
        }

        $query->where(implode(' ', $subSelect->getPart(\Zend_Db_Select::WHERE)));
    }
}
