<?php

namespace CustomerManagementFramework\Listing\Filter;

use CustomerManagementFramework\Listing\ListingFilterInterface;
use Pimcore\Model\Object\Listing as CoreListing;

abstract class AbstractFieldValue extends AbstractField implements ListingFilterInterface
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
     * Apply filter to listing
     *
     * @param CoreListing\Concrete|CoreListing\Dao $listing
     */
    public function applyToListing(CoreListing\Concrete $listing)
    {
        if (empty($this->value)) {
            return;
        }

        $value     = $this->processValue($this->value);
        $condition = sprintf(
            '`%s`.`%s` %s ?',
            $this->getTableName($listing->getClassId()),
            $this->field,
            $this->getComparisonOperator()
        );

        $listing->addConditionParam($condition, $value);
    }

    /**
     * @return string
     */
    abstract protected function getComparisonOperator();
}
