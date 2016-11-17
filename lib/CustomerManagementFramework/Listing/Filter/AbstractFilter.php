<?php

namespace CustomerManagementFramework\Listing\Filter;

abstract class AbstractFilter
{
    /**
     * @var string
     */
    protected $tableName;

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

        return $prefix . (int)$classId;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }
}
