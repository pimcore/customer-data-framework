<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
