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

namespace CustomerManagementFrameworkBundle\Model;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;

abstract class AbstractObjectActivity extends \Pimcore\Model\DataObject\Concrete implements PersistentActivityInterface
{
    public function cmfIsActive()
    {
        return $this->getPublished() && ($this->getCustomer() instanceof CustomerInterface);
    }

    public function cmfGetActivityDate()
    {
        return Carbon::createFromTimestamp($this->getCreationDate());
    }

    public function cmfUpdateOnSave()
    {
        return true;
    }

    /**
     * @return string
     */
    public function cmfGetType()
    {
        return $this->getClassName();
    }

    /**
     * @inheritdoc
     */
    public function cmfToArray()
    {
        $fieldDefintions = $this->getClass()->getFieldDefinitions();

        $result = [];

        foreach ($fieldDefintions as $fd) {
            $fieldName = $fd->getName();
            $result[$fieldName] = $fd->getForWebserviceExport($this);
        }

        unset($result['customer']);

        $result['o_id'] = $this->getId();
        $result['o_key'] = $this->getKey();
        $result['o_path'] = $this->getRealFullPath();

        return $result;
    }

    public static function cmfGetAttributeDataTypes()
    {
        return false;
    }

    public function cmfUpdateData(array $data)
    {
        throw new \Exception('update of pimcore object activities not allowed');
    }

    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        $object = null;
        if (!empty($data['o_id'])) {
            $object = self::getById($data['o_id']);

        }

        if(is_null($object)) {
            $object = new static;
        }

        if ($fromWebservice) {
            $object->setValues($data['attributes']);
        } else {
            $object->setValues($data);
        }

        return $object;
    }

    public function cmfWebserviceUpdateAllowed()
    {
        return false;
    }

    public static function cmfGetOverviewData(ActivityStoreEntryInterface $entry)
    {
        return false;
    }

    public static function cmfGetDetailviewData(ActivityStoreEntryInterface $entry)
    {
        $attributes = $entry->getAttributes();

        return \Pimcore::getContainer()->get('cmf.activity_view')->formatAttributes(
            $entry->getImplementationClass(),
            $attributes
        );
    }

    public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry)
    {
        return false;
    }
}
