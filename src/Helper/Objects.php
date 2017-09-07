<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\File;
use Pimcore\Model\Element\Service;
use Pimcore\Model\Object\Concrete;

class Objects
{
    public static function getValidKey($key)
    {
        if (!method_exists('Pimcore\Model\Element\Service', 'getValidKey')) {
            return File::getValidFilename($key);
        }

        return Service::getValidKey($key, 'object');
    }

    public static function checkObjectKey(Concrete $object)
    {
        self::checkObjectKeyHelper($object);
    }

    private static function checkObjectKeyHelper(Concrete $object, $origKey = null, $keyCounter = 1)
    {
        $origKey = is_null($origKey) ? self::getValidKey($object->getKey()) : $origKey;

        $list = new \Pimcore\Model\Object\Listing;
        $list->setUnpublished(true);
        $list->setCondition(
            "o_path = '".(string)$object->getParent()."/' and o_key = '".$object->getKey(
            )."' and o_id != ".$object->getId()
        );
        $list->setLimit(1);
        $list = $list->load();

        if (sizeof($list)) {
            $keyCounter++;
            $object->setKey($origKey.'-'.$keyCounter);
            self::checkObjectKeyHelper($object, $origKey, $keyCounter);
        }
    }

    /**
     * add pimcore objects to given array if element are not already part of the array
     * - returns true if data in array got changed
     *
     * @param array $array
     * @param array $addObjects
     *
     * @return false|array
     */
    public static function addObjectsToArray(array &$array, array $addObjects)
    {
        $added = [];
        foreach ($addObjects as $addObject) {
            $found = false;
            foreach ($array as $object) {
                if ($addObject->getId() == $object->getId()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $added[] = $addObject;
                $array[] = $addObject;
            }
        }

        return sizeof($added) ? $added : false;
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
     * remove pimcore objects from given array
     * - returns true if data in array got changed
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
            foreach ($removeObjects as $removeObject) {
                if (!method_exists($removeObject, 'getId')) {
                    continue;
                }

                if ($object->getId() == $removeObject->getId()) {
                    $removed[] = $removeObject;
                    unset($array[$key]);
                }
            }
        }

        if (sizeof($removed)) {
            $array = array_values($array);
        }

        return sizeof($removed) ? $removed : false;
    }
}
