<?php

namespace CustomerManagementFramework\Listing\Filter;

use Pimcore\Model\Object\Listing as CoreListing;

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
