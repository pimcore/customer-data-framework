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

abstract class AbstractFilter
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param int $classId
     * @param string $prefix
     *
     * @return string
     */
    protected function getTableName($classId, $prefix = 'object_')
    {
        if (null !== $this->tableName) {
            return $this->tableName;
        }

        return $prefix.(int)$classId;
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
