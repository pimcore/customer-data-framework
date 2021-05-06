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

namespace CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition\Listing;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Pimcore\Db\ConnectionInterface;
use Pimcore\Model;

/**
 * @property FilterDefinition\Listing $model
 * @property ConnectionInterface $db
 */
class Dao extends Model\Listing\Dao\AbstractDao
{
    /**
     * Loads a list of static routes for the specicifies parameters, returns an array of Staticroute elements
     *
     * @return FilterDefinition[]
     */
    public function load()
    {
        // fetch ids with conditions, order and offset
        $filterDefinitionsData = $this->db->fetchCol('SELECT id FROM ' . FilterDefinition\Dao::TABLE_NAME . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());
        // prepare filter definitions object array
        $filterDefinitions = [];
        // go through found ids
        foreach ($filterDefinitionsData as $filterDefinitionId) {
            // load object
            $filterDefinitions[] = FilterDefinition::getById($filterDefinitionId);
        }
        $this->model->setFilterDefinitions($filterDefinitions);

        return $filterDefinitions;
    }

    public function getTotalCount()
    {
        return $this->db->fetchOne('SELECT count(*) FROM ' . FilterDefinition\Dao::TABLE_NAME . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());
    }
}
