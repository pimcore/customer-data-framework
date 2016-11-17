<?php

namespace CustomerManagementFramework\Listing\Filter;

use CustomerManagementFramework\Listing\OnCreateQueryFilterInterface;
use Pimcore\Model\Object\Listing as CoreListing;

abstract class AbstractFieldValue extends AbstractField implements OnCreateQueryFilterInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    protected $inverse = false;

    /**
     * @param string $field
     * @param string $value
     * @param bool $inverse
     */
    public function __construct($field, $value, $inverse = false)
    {
        parent::__construct($field);

        $this->value   = trim($value);
        $this->inverse = (bool)$inverse;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function processValue($value)
    {
        return $value;
    }

    /**
     * Apply filter directly to query
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     * @param \Zend_Db_Select $query
     */
    public function applyOnCreateQuery(CoreListing\Concrete $listing, \Zend_Db_Select $query)
    {
        if (empty($this->value)) {
            return;
        }

        $value = $this->processValue($this->value);
        $query->where(sprintf(
            '`%s`.`%s` %s ?',
            $this->getTableName($listing->getClassId()),
            $this->field,
            $this->getComparisonOperator()
        ), $value);
    }

    /**
     * @return string
     */
    abstract protected function getComparisonOperator();
}
