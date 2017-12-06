<?php
/**
 * Created by PhpStorm.
 * User: dschroffner
 * Date: 04.12.2017
 * Time: 14:54
 */

namespace CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;

use CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition;
use Pimcore\Db\Connection;
use Pimcore\Model\Dao\AbstractDao;

/**
 * Class Dao
 * @package CustomerManagementFrameworkBundle\Model\CustomerView\FilterDefinition
 * @property FilterDefinition model
 * @property Connection db
 */
class Dao extends AbstractDao {

    /**
     * Table name for class
     */
    const TABLE_NAME = 'plugin_cmf_customer_filter_definition';

    const ATTRIBUTE_ID = 'id';
    const ATTRIBUTE_NAME = 'name';
    const ATTRIBUTE_DEFINITION = 'definition';
    const ATTRIBUTE_ALLOWED_USER_IDS = 'allowedUserIds';
    const ATTRIBUTE_SHOW_SEGMENTS = 'showSegments';
    const ATTRIBUTE_READ_ONLY = 'readOnly';
    const ATTRIBUTE_SHORTCUT_AVAILABLE = 'shortcutAvailable';
    const ATTRIBUTE_CREATION_DATE = 'creationDate';
    const ATTRIBUTE_MODIFICATION_DATE = 'modificationDate';

    /**
     * @param null $id
     *
     * @throws \Exception
     */
    public function getById($id = null)
    {
        if ($id != null) {
            $this->model->setId($id);
        }
        $data = $this->db->fetchAssoc("SELECT * FROM " . self::TABLE_NAME . " WHERE " . self::ATTRIBUTE_ID . "=".intval($id));
        if (isset($data[self::ATTRIBUTE_ID])) {
            $data[self::ATTRIBUTE_DEFINITION] = json_decode($data[self::ATTRIBUTE_DEFINITION], true);
            $data[self::ATTRIBUTE_ALLOWED_USER_IDS] = explode(',', $data[self::ATTRIBUTE_ALLOWED_USER_IDS]);
            $data[self::ATTRIBUTE_SHOW_SEGMENTS] = explode(',', $data[self::ATTRIBUTE_SHOW_SEGMENTS]);
            $this->assignVariablesToModel($data);
        } else {
            throw new \Exception('Route with id: ' . $this->model->getId() . ' does not exist');
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

        try {
            if(!is_null($this->model->getId())) {
                $data['id'] = $this->model->getId();
            } else $data = [];
            $data = array_merge($data,[
                self::ATTRIBUTE_NAME => $this->model->getName(),
                self::ATTRIBUTE_DEFINITION => json_encode($this->model->getDefinition()),
                self::ATTRIBUTE_ALLOWED_USER_IDS => implode(',',$this->model->getAllowedUserIds()),
                self::ATTRIBUTE_SHOW_SEGMENTS => implode(',',$this->model->getShowSegments()),
                self::ATTRIBUTE_READ_ONLY => ($this->model->isReadOnly())?1:0,
                self::ATTRIBUTE_SHORTCUT_AVAILABLE => ($this->model->isShortcutAvailable())?1:0,
                self::ATTRIBUTE_CREATION_DATE => $this->model->getCreationDate(),
                self::ATTRIBUTE_MODIFICATION_DATE => $this->model->getModificationDate(),
            ]);
            $this->db->insertOrUpdate(self::TABLE_NAME, $data);
        } catch (\Exception $e) {
            throw $e;
        }

        if (!$this->model->getId()) {
            $this->model->setId($this->db->lastInsertId());
        }
    }
}