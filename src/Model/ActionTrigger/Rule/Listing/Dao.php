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
