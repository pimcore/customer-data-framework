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

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule\Listing;

use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use Doctrine\DBAL\Exception;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{
    /**
     * @return Rule[]
     *
     * @throws Exception
     */
    public function load(): array
    {
        $rules = [];

        $ids = $this->db->fetchFirstColumn(
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
     *
     * @throws Exception
     */
    public function getTotalCount(): int
    {
        return (int)$this->db->fetchOne(
            'SELECT COUNT(*) as amount FROM '.Rule\Dao::TABLE_NAME.' '.$this->getCondition(),
            $this->model->getConditionVariables()
        );
    }
}
