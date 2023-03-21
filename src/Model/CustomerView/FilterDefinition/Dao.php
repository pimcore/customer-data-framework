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

namespace CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Doctrine\DBAL\Connection;
use Pimcore\Db\Helper;
use Pimcore\Logger;
use Pimcore\Model\Dao\AbstractDao;
use Pimcore\Model\Dao\DaoTrait;

/**
 * Class Dao
 *
 * @package CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition
 *
 * @property FilterDefinition $model
 * @property Connection $db
 */
class Dao extends AbstractDao
{
    use DaoTrait {
        assignVariablesToModel as protected assignVariablesToModelDao;
    }

    /**
     * Table name for class
     */
    const TABLE_NAME = 'plugin_cmf_customer_filter_definition';

    const ATTRIBUTE_ID = 'id';
    const ATTRIBUTE_OWNER_ID = 'ownerId';
    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_DEFINITION = 'definition';
    const ATTRIBUTE_ALLOWED_USER_IDS = 'allowedUserIds';
    const ATTRIBUTE_READ_ONLY = 'readOnly';
    const ATTRIBUTE_SHORTCUT_AVAILABLE = 'shortcutAvailable';
    const ATTRIBUTE_CREATION_DATE = 'creationDate';
    const ATTRIBUTE_MODIFICATION_DATE = 'modificationDate';

    /**
     * @param int|null $id
     *
     * @throws \Exception
     */
    public function getById($id = null): void
    {
        $data = $this->db->fetchAssociative(
            'SELECT * FROM '. self::TABLE_NAME . ' WHERE ' . self::ATTRIBUTE_ID . ' = ?',
            [(int) $id]
        );
        $this->assignVariablesToModel($data);
    }

    /**
     * @param string $name
     *
     * @throws \Exception Throws exception if object with id not found
     */
    public function getByName(string $name)
    {
        $data = $this->db->fetchAssociative(
            'SELECT * FROM '. self::TABLE_NAME . ' WHERE ' . self::ATTRIBUTE_NAME . ' = ?',
            [$name]
        );
        $this->assignVariablesToModel($data);
    }

    /**
     * Try to set db data to model. Extend the assignVariablesToModel from DaoTrait to convert attributes in one step
     *
     * @param array $data data from sql query
     *
     * @throws \Exception Throws exception if object with id not found
     */
    protected function assignVariablesToModel($data): void
    {
        if (isset($data[self::ATTRIBUTE_ID])) {
            $data[self::ATTRIBUTE_DEFINITION] = json_decode($data[self::ATTRIBUTE_DEFINITION], true);
            $data[self::ATTRIBUTE_ALLOWED_USER_IDS] = explode(',', $data[self::ATTRIBUTE_ALLOWED_USER_IDS]);
            $this->assignVariablesToModelDao($data);
        } else {
            throw new \Exception('FilterDefinition with id "' . $this->model->getId() . '" does not exist');
        }
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        $datetime = date('Y-m-d H:i:s');
        if (!$this->model->getCreationDate()) {
            $this->model->setCreationDate($datetime);
        }
        $this->model->setModificationDate($datetime);

        if (!is_null($this->model->getId())) {
            $data['id'] = $this->model->getId();
        } else {
            $data = [];
        }
        $data = array_merge($data, [
            self::ATTRIBUTE_OWNER_ID => $this->model->getOwnerId(),
            self::ATTRIBUTE_NAME => $this->model->getName(),
            self::ATTRIBUTE_DEFINITION => json_encode($this->model->getDefinition()),
            self::ATTRIBUTE_ALLOWED_USER_IDS => implode(',', $this->model->getAllowedUserIds()),
            self::ATTRIBUTE_READ_ONLY => $this->model->isReadOnly(),
            self::ATTRIBUTE_SHORTCUT_AVAILABLE => $this->model->isShortcutAvailable(),
            self::ATTRIBUTE_CREATION_DATE => $this->model->getCreationDate(),
            self::ATTRIBUTE_MODIFICATION_DATE => $this->model->getModificationDate(),
        ]);
        Helper::upsert($this->db, self::TABLE_NAME, $data, $this->getPrimaryKey(self::TABLE_NAME));

        if (!$this->model->getId()) {
            // TODO insecure, could be another id
            $this->model->setId((int) $this->db->lastInsertId());
        }
    }

    /**
     * Deletes the object
     *
     * @return bool Return true on deletion success otherwise false
     *
     * @throws \Exception Throws exception if object not found in database
     */
    public function delete(): bool
    {
        try {
            $this->db->delete(self::TABLE_NAME, [
                self::ATTRIBUTE_ID => $this->model->getId(),
            ]);
        } catch (\Exception $e) {
            Logger::alert('Could not delete filter definition. Not found in database.', ['id' => $this->model->getId()]);
            throw $e;
        }

        return true;
    }
}
