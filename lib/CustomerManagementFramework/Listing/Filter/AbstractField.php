<?php

namespace CustomerManagementFramework\Listing\Filter;

abstract class AbstractField extends AbstractFilter
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param string $field
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * @param int $classId
     * @param string $prefix
     * @return string
     */
    protected function getTableName($classId, $prefix = 'object_')
    {
        if (null !== $this->tableName) {
            return $this->tableName;
        }

        return parent::getTableName($classId, $prefix);
    }

    /**
     * @param string $tableName
     * @return AbstractField
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }
}
