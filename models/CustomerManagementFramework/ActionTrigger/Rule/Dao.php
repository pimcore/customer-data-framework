<?php

namespace CustomerManagementFramework\ActionTrigger\Rule;

use CustomerManagementFramework\ActionTrigger\ActionDefinition;
use CustomerManagementFramework\ActionTrigger\TriggerDefinition;
use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;
use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
{
    const TABLE_NAME = 'plugin_cmf_actiontrigger_rules';

    public function getById($id)
    {

        $raw = $this->db->fetchRow("SELECT * FROM " . self::TABLE_NAME . " WHERE id = ?", $id);


        if($raw['trigger']) {

            $triggers = [];
            $triggerData = json_decode($raw['trigger'], true);
            foreach($triggerData as $triggerDefinitionData) {
                $triggers[] = new TriggerDefinition($triggerDefinitionData);
            }

            $raw['trigger'] = $triggers;
        } else {
            $raw['trigger'] = null;
        }

        if ($raw["id"]) {
            $this->assignVariablesToModel($raw);

            $actionIds = $this->db->fetchCol("select id from " . \CustomerManagementFramework\ActionTrigger\ActionDefinition\Dao::TABLE_NAME . " where ruleId = ?", $raw['id']);

            $actions = [];
            foreach($actionIds as $id) {
                $actions[] = ActionDefinition::getById($id);
            }

            $this->model->setAction($actions);

        } else {
            throw new \Exception("Action trigger rule with ID " . $id . " doesn't exist");
        }
    }

    protected $lastErrorCode = null;
    public function save() {

        $triggerData = [];
        if($triggers = $this->model->getTrigger()) {
            foreach($triggers as $trigger) {
                /**
                 * @var TriggerDefinitionInterface $trigger
                 */
                $triggerData[] = $trigger->getDefinitionData();
            }
        }

        $data = [
            'name' => $this->model->getName(),
            'label' => $this->model->getLabel(),
            'description' => $this->model->getDescription(),
            'active' => (int)$this->model->getActive(),
            'trigger' => sizeof($triggerData) ? json_encode($triggerData) : null,
        ];


        if($this->model->getId()) {
            $this->db->beginTransaction();
            try {

                $this->saveActions();

                $this->db->update(self::TABLE_NAME , $data, $this->db->quoteInto("id = ?", $this->model->getId()));
                $this->db->commit();
            } catch(\Exception $e) {

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
                $this->model->setId($this->db->fetchOne("SELECT LAST_INSERT_ID();"));
                $this->model->setCreationDate($data['creationDate']);

                $this->saveActions();
                $this->db->commit();

            } catch(\Exception $e) {
                $this->db->rollBack();

                $this->lastErrorCode = $e->getCode();
                return false;
            }
        }


        return true;
    }

    private function saveActions() {

        $savedActionIds = [-1];

        if($actions = $this->model->getAction()) {
            foreach($actions as $action) {
                $action->setRuleId($this->model->getId());
                $action->save();
                $savedActionIds[] = $action->getId();
            }
        }

        $this->db->delete(\CustomerManagementFramework\ActionTrigger\ActionDefinition\Dao::TABLE_NAME, "ruleId = " . $this->model->getId() . " and id not in(" . implode(',', $savedActionIds) . ")");
    }

    public function delete() {

        $this->db->beginTransaction();
        try {

            $this->db->delete(self::TABLE_NAME, $this->db->quoteInto("id = ?", $this->model->getId()));

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getLastErrorCode() {
        return $this->lastErrorCode;
    }
}

