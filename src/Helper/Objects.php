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

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\File;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Service as DataObjectService;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Service;

class Objects
{
    public static function getValidKey($key)
    {
        if (!method_exists('Pimcore\Model\Element\Service', 'getValidKey')) {
            $result = File::getValidFilename($key);
        } else {
            $result = Service::getValidKey($key, 'object');
        }

        return str_replace('%', '-', $result);
    }

    public static function checkObjectKey(Concrete $object)
    {
        self::checkObjectKeyHelper($object);
    }

    private static function checkObjectKeyHelper(Concrete $object, $origKey = null, $keyCounter = 1)
    {
        $origKey = is_null($origKey) ? self::getValidKey($object->getKey()) : $origKey;
        $pathField = DataObjectService::getVersionDependentDatabaseColumnName('path');
        $keyField = DataObjectService::getVersionDependentDatabaseColumnName('key');
        $idField = DataObjectService::getVersionDependentDatabaseColumnName('id');
        $notUnique = true;
        while ($notUnique) {
            $list = new \Pimcore\Model\DataObject\Listing;
            $list->setUnpublished(true);
            $list->addConditionParam(
                $pathField . ' = ? and `' . $keyField . '` = ?',
                [(string)$object->getParent() . '/', $object->getKey()]
            );
            $objectId = $object->getId();
            if ($objectId !== null) {
                $list->addConditionParam($idField . ' != ?', $object->getId());
            } else {
                $list->addConditionParam($idField . ' is not null');
            }
            $list->setLimit(1);
            $list = $list->load();

            if (sizeof($list)) {
                $keyCounter++;
                $object->setKey($origKey.'-'.$keyCounter);
            } else {
                $notUnique = false;
            }
        }
    }

    /**
     * add pimcore objects to given array if element are not already part of the array
     * works with arrays of objects and arrays of objects with metadata
     *
     * - returns false if no data in array was changed
     * - returns array with added objects if object where added
     *
     * @param array $array
     * @param array $addObjects
     *
     * @return false|array
     */
    public static function addObjectsToArray(array &$array, array $addObjects)
    {
        $added = [];
        foreach ($addObjects as $add) {
            $addObject = $add instanceof ObjectMetadata ? $add->getObject() : $add;

            if (!method_exists($addObject, 'getId')) {
                continue;
            }

            $found = false;
            foreach ($array as $object) {
                $object = $object instanceof ObjectMetadata ? $object->getObject() : $object;

                if ($addObject->getId() == $object->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $added[] = $add;
                $array[] = $add;
            }
        }

        return sizeof($added) ? $added : false;
    }

    /**
     * remove pimcore objects from given array
     * works with arrays of objects and arrays of objects with metadata
     *
     * - returns false if no data in array was changed
     * - returns array with removed objects if object where removed
     *
     * @param array $array
     * @param array $removeObjects
     *
     * @return false|array
     */
    public static function removeObjectsFromArray(array &$array, array $removeObjects)
    {
        $removed = [];

        foreach ($array as $key => $object) {
            $object = $object instanceof ObjectMetadata ? $object->getObject() : $object;

            foreach ($removeObjects as $remove) {
                $removeObject = $remove instanceof ObjectMetadata ? $remove->getObject() : $remove;

                if (!method_exists($removeObject, 'getId')) {
                    continue;
                }

                if ($object->getId() == $removeObject->getId()) {
                    $removed[] = $remove;
                    unset($array[$key]);
                }
            }
        }

        if (sizeof($removed)) {
            $array = array_values($array);
        }

        return sizeof($removed) ? $removed : false;
    }

    public static function objectArrayUnique($array)
    {
        $result = [];

        foreach ($array as $object) {
            $result[$object->getId()] = $object;
        }

        return array_values($result);
    }

    /**
     * Returns IDs of an array of objects
     *
     * @param array $array
     *
     * @return array
     */
    public static function getIdsFromArray(array &$array)
    {
        $ids = [];
        foreach ($array as $object) {
            $ids[] = $object->getId();
        }

        return $ids;
    }

    /**
     * Returns true if the given object or (object with metadata item) is contained in the $addSegments array.
     * Objects with metdata are only matched by object IDs (not by metadata or concrete instances).
     *
     * @param mixed $object
     * @param array $objects
     *
     * @return bool;
     */
    public static function objectInArray($object, array $objects)
    {
        $object = $object instanceof ObjectMetadata ? $object->getObject() : $object;
        if (!$object instanceof ElementInterface) {
            return false;
        }

        if (!$object->getId()) {
            return false;
        }

        foreach ($objects as $_object) {
            $_object = $_object instanceof ObjectMetadata ? $_object->getObject() : $_object;
            if (!$_object instanceof ElementInterface) {
                continue;
            }

            if ($_object->getId() == $object->getId()) {
                return true;
            }
        }

        return false;
    }
}
