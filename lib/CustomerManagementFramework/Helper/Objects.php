<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 16.11.2016
 * Time: 16:48
 */

namespace CustomerManagementFramework\Helper;


use Pimcore\File;
use Pimcore\Model\Element\Service;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\Concrete;

class Objects {

    public static function getValidKey($key)
    {
        if(!method_exists('Pimcore\Model\Element\Service', 'getValidKey')) {
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
        $list->setCondition("o_path = '".(string)$object->getParent()."/' and o_key = '".$object->getKey()."' and o_id != " . $object->getId());
        $list->setLimit(1);
        $list = $list->load();

        if(sizeof($list)) {
            $keyCounter++;
            $object->setKey($origKey . '-' . $keyCounter);
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
     * @return bool
     */
    public static function addObjectsToArray(array &$array, array $addObjects)
    {
        $changed = false;
        foreach ($addObjects as $addObject) {
            $found = false;
            foreach ($array as $object) {
                if ($addObject->getId() == $object->getId()) {
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $changed = true;
                $array[] = $addObject;
            }
        }

        return $changed;
    }

    /**
     * remove pimcore objects from given array
     * - returns true if data in array got changed
     *
     * @param array $array
     * @param array $removeObjects
     *
     * @return bool
     */
    public static function removeObjectsFromArray(array &$array, array $removeObjects)
    {
        $changed = false;

        foreach($array as $key => $object)
        {
            foreach($removeObjects as $removeObject) {

                if(!method_exists($removeObject, 'getId')) {
                    continue;
                }

                if($object->getId() == $removeObject->getId()) {
                    unset($array[$key]);
                    $changed = true;
                }
            }
        }

        if($changed) {
            $array = array_values($array);
        }


        return $changed;
    }
}