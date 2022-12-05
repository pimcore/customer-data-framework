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

namespace CustomerManagementFrameworkBundle\Model;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Service\ObjectToArray;
use Exception;
use Pimcore;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Service;

abstract class AbstractObjectActivity extends Concrete implements PersistentActivityInterface
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
        $result = ObjectToArray::getInstance()->toArray($this);
        unset($result['customer']);

        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $pathField = Service::getVersionDependentDatabaseColumnName('path');
        $keyField = Service::getVersionDependentDatabaseColumnName('key');
        $result[$idField] = $this->getId();
        $result[$pathField] = $this->getKey();
        $result[$keyField] = $this->getRealFullPath();

        return $result;
    }

    public static function cmfGetAttributeDataTypes()
    {
        return false;
    }

    public function cmfUpdateData(array $data)
    {
        throw new Exception('update of pimcore object activities not allowed');
    }

    public static function cmfCreate(array $data, $fromWebservice = false)
    {
        $object = null;
        $idField = Service::getVersionDependentDatabaseColumnName('id');
        if (!empty($data[$idField])) {
            $object = self::getById($data[$idField]);
        }

        if (is_null($object)) {
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

        return Pimcore::getContainer()->get('cmf.activity_view')->formatAttributes(
            $entry->getImplementationClass(),
            $attributes
        );
    }

    public static function cmfGetDetailviewTemplate(ActivityStoreEntryInterface $entry)
    {
        return false;
    }
}
