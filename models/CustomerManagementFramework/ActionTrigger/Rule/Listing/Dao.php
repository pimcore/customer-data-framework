<?php

namespace CustomerManagementFramework\ActionTrigger\Rule\Listing;

use CustomerManagementFramework\ActionTrigger\Rule;
use Pimcore\Model;

class Dao extends Model\Listing\Dao\AbstractDao
{

    public function load()
    {
        $rules = [];

        $ids = $this->db->fetchCol("SELECT id FROM " . Rule\Dao::TABLE_NAME . " " . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

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
        return (int) $this->db->fetchOne("SELECT COUNT(*) as amount FROM " . Rule\Dao::TABLE_NAME . " " . $this->getCondition(), $this->model->getConditionVariables());
    }

}
