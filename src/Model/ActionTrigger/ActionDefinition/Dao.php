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

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\ActionDefinition;

use Pimcore\Model;

class Dao extends Model\Dao\AbstractDao
{
    const TABLE_NAME = 'plugin_cmf_actiontrigger_actions';

    public function getById($id)
    {
        $raw = $this->db->fetchRow('SELECT * FROM '.self::TABLE_NAME.' WHERE id = ?', $id);

        if ($raw['id']) {
            $raw['options'] = json_decode($raw['options'], true);
            $this->assignVariablesToModel($raw);
        } else {
            throw new \Exception('Action trigger rule with ID '.$id." doesn't exist");
        }
    }

    protected $lastErrorCode = null;

    public function save()
    {
        $data = [
            'id' => $this->model->getId(),
            'ruleId' => $this->model->getRuleId(),
            'actionDelay' => $this->model->getActionDelay(),
            'implementationClass' => $this->model->getImplementationClass(),
            'options' => json_encode($this->model->getOptions()),
        ];

        if ($this->model->getId()) {
            $this->db->updateWhere(self::TABLE_NAME, $data, $this->db->quoteInto('id = ?', $this->model->getId()));
        } else {
            $data['creationDate'] = time();
            unset($data['id']);

            $this->db->insert(self::TABLE_NAME, $data);

            $this->model->setId($this->db->fetchOne('SELECT LAST_INSERT_ID();'));
            $this->model->setCreationDate($data['creationDate']);
        }

        return true;
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
