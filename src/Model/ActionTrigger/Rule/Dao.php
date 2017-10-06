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

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;

use CustomerManagementFrameworkBundle\ActionTrigger\Condition\ConditionDefinitionInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\ActionDefinition;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\ConditionDefinition;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\TriggerDefinition;
use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
{
    const TABLE_NAME = 'plugin_cmf_actiontrigger_rules';

    public function getById($id)
    {
        $raw = $this->db->fetchRow('SELECT * FROM '.self::TABLE_NAME.' WHERE id = ?', $id);

        if ($raw['trigger']) {
            $triggers = [];
            $triggerData = json_decode($raw['trigger'], true);
            foreach ($triggerData as $triggerDefinitionData) {
                $triggers[] = new TriggerDefinition($triggerDefinitionData);
            }

            $raw['trigger'] = $triggers;
        } else {
            $raw['trigger'] = [];
        }

        if ($raw['condition']) {
            $conditions = [];
            $conditionData = json_decode($raw['condition'], true);
            foreach ($conditionData as $conditionDefinitionData) {
                $conditions[] = new ConditionDefinition($conditionDefinitionData);
            }

            $raw['condition'] = $conditions;
        } else {
            $raw['condition'] = [];
        }

        if ($raw['id']) {
            $this->assignVariablesToModel($raw);

            $actionIds = $this->db->fetchCol(
                'select id from '.ActionDefinition\Dao::TABLE_NAME.' where ruleId = ?',
                $raw['id']
            );

            $actions = [];
            foreach ($actionIds as $id) {
                $actions[] = ActionDefinition::getById($id);
            }

            $this->model->setAction($actions);
        } else {
            throw new \Exception('Action trigger rule with ID '.$id." doesn't exist");
        }
    }

    protected $lastErrorCode = null;

    public function save()
    {
        $triggerData = [];
        if ($triggers = $this->model->getTrigger()) {
            foreach ($triggers as $trigger) {
                /**
                 * @var TriggerDefinitionInterface $trigger
                 */
                $triggerData[] = $trigger->getDefinitionData();
            }
        }

        $conditionData = [];
        if ($conditions = $this->model->getCondition()) {
            foreach ($conditions as $condition) {
                /**
                 * @var ConditionDefinitionInterface $condition
                 */
                $conditionData[] = $condition->getDefinitionData();
            }
        }

        $data = [
            'name' => $this->model->getName(),
            'description' => $this->model->getDescription(),
            'active' => (int)$this->model->getActive(),
            'trigger' => sizeof($triggerData) ? json_encode($triggerData) : null,
            'condition' => sizeof($conditionData) ? json_encode($conditionData) : null,
            'modificationDate' => time(),
        ];

        if ($this->model->getId()) {
            $this->db->beginTransaction();
            try {
                $this->saveActions();

                $this->db->updateWhere(self::TABLE_NAME, $data, $this->db->quoteInto('id = ?', $this->model->getId()));
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->lastErrorCode = $e->getCode();

                return false;
            }
        } else {
            $data['creationDate'] = time();
            unset($data['id']);
            $this->db->beginTransaction();
            try {
                $this->db->insert(self::TABLE_NAME, $data);
                $this->model->setId($this->db->fetchOne('SELECT LAST_INSERT_ID();'));
                $this->model->setCreationDate($data['creationDate']);
                $this->saveActions();
                $this->db->commit();
            } catch (\Exception $e) {
                $this->db->rollBack();
                $this->lastErrorCode = $e->getCode();

                return false;
            }
        }

        return true;
    }

    private function saveActions()
    {
        $savedActionIds = [-1];

        if ($actions = $this->model->getAction()) {
            foreach ($actions as $action) {
                $action->setRuleId($this->model->getId());
                $action->save();
                $savedActionIds[] = $action->getId();
            }
        }

        $this->db->deleteWhere(
            ActionDefinition\Dao::TABLE_NAME,
            'ruleId = '.$this->model->getId().' and id not in('.implode(',', $savedActionIds).')'
        );
    }

    public function delete()
    {
        $this->db->beginTransaction();
        try {
            $this->db->deleteWhere(self::TABLE_NAME, $this->db->quoteInto('id = ?', $this->model->getId()));

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getLastErrorCode()
    {
        return $this->lastErrorCode;
    }
}
