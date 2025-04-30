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

abstract class AbstractFilter
{
    /**
     * @var string|null
     */
    protected $tableName;

    /**
     * @param string $classId
     * @param string $prefix
     *
     * @return string
     */
    protected function getTableName($classId, $prefix = 'object_')
    {
        if (null !== $this->tableName) {
            return $this->tableName;
        }

        return $prefix.$classId;
    }

    /**
     * @param string $tableName
     *
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }
}
