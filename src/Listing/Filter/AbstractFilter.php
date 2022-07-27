<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
