<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:11
 */

namespace CustomerManagementFrameworkBundle\Service;

use Pimcore\Model\Object\Concrete;

class ObjectToArray {
    private function __construct()
    {

    }

    /**
     * @return static
     */
    private static $instance;
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    public function toArray(Concrete $object) {
        $fieldDefintions = $object->getClass()->getFieldDefinitions();

        $result = [];


        $result['id']  = $object->getId();

        foreach($fieldDefintions as $fd)
        {
            $fieldName = $fd->getName();
            $result[$fieldName] = $fd->getForWebserviceExport($object);
        }


        $result['modificationDate'] = $object->getModificationDate();
        $result['creationDate'] = $object->getCreationDate();

        return $result;
    }
}