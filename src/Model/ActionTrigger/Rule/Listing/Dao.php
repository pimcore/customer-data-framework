<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule\Listing;

use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    public function load()
    {
        $rules = [];

        $ids = $this->db->fetchCol(
            'SELECT id FROM '.Rule\Dao::TABLE_NAME.' '.$this->getCondition().$this->getOrder().$this->getOffsetLimit(),
            $this->model->getConditionVariables()
        );

        foreach ($ids as $id) {
            $rules[] = Rule::getById($id);
        }

        return $rules;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return (int)$this->db->fetchOne(
            'SELECT COUNT(*) as amount FROM '.Rule\Dao::TABLE_NAME.' '.$this->getCondition(),
            $this->model->getConditionVariables()
        );
    }
}
