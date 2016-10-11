<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFramework\Model;

abstract class AbstractCustomer extends \Pimcore\Model\Object\Concrete implements ICustomer{

    public function cmfToArray()
    {
        $fieldDefintions = $this->getClass()->getFieldDefinitions();

        $result = [];

        foreach($fieldDefintions as $fd)
        {
            $fieldName = $fd->getName();
            $result[$fieldName] = $fd->getForWebserviceExport($this);
        }

        $result['o_id']  = $this->getId();
        $result['o_key'] = $this->getKey();

        return $result;
    }
}