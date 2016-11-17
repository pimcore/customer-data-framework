<?php

namespace CustomerManagementFramework\Listing\Filter;

use CustomerManagementFramework\Listing\FilterInterface;

abstract class AbstractFilter
{
    /**
     * @param int $classId
     * @param string $prefix
     * @return string
     */
    protected function getTableName($classId, $prefix = 'object_')
    {
        return $prefix . (int)$classId;
    }
}
