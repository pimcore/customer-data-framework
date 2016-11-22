<?php

namespace CustomerManagementFramework\ActionTrigger\Rule;

use CustomerManagementFramework\ActionTrigger\Trigger\TriggerInterface;
use CustomerManagementFramework\Factory;
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
                $triggers[] = Factory::getInstance()->createActionTriggerObject($triggerDefinitionData);
            }

            $raw['trigger'] = $triggers;
        } else {
            $raw['trigger'] = null;
        }

        if ($raw["id"]) {
            $this->assignVariablesToModel($raw);

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
                 * @var TriggerInterface $trigger
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
            try {
                $this->db->update(self::TABLE_NAME , $data, $this->db->quoteInto("id = ?", $this->model->getId()));
            } catch(\Exception $e) {

                $this->lastErrorCode = $e->getCode();
                return false;
            }
        } else {
            $data['creationDate'] = time();
            unset($data['id']);

            try {
                $this->db->insert("plugin_form_forms", $data);
                $this->model->setId($this->db->fetchOne("SELECT LAST_INSERT_ID();"));
                $this->model->setCreationDate($data['creationDate']);
            } catch(\Exception $e) {
                $this->lastErrorCode = $e->getCode();
                return false;
            }
        }


        return true;
    }

    public function delete() {

        $this->db->beginTransaction();
        try {

            $this->db->delete("plugin_form_forms", $this->db->quoteInto("id = ?", $this->model->getId()));
            $this->db->delete("plugin_form_forms", $this->db->quoteInto("idPath LIKE ?", $this->model->getIdPath() . $this->model->getId() . "/%"));

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

