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

namespace CustomerManagementFrameworkBundle\Model;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFrameworkBundle\Service\ObjectToArray;
use Exception;
use Pimcore;
use Pimcore\Model\DataObject\Concrete;

abstract class AbstractObjectActivity extends Concrete implements PersistentActivityInterface
{
    public function cmfIsActive()
    {
        return $this->getPublished() && ($this->getCustomer() instanceof CustomerInterface);
    }

    public function cmfGetActivityDate()
    {
        return Carbon::createFromTimestamp($this->getCreationDate(), date_default_timezone_get());
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

    public function cmfToArray()
    {
        $result = ObjectToArray::getInstance()->toArray($this);
        unset($result['customer']);

        $result['id'] = $this->getId();
        $result['key'] = $this->getKey();
        $result['path'] = $this->getRealFullPath();

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
        $idField = 'id';
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
