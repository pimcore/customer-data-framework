<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFrameworkBundle\Model;


use CustomerManagementFrameworkBundle\Service\ObjectToArray;

abstract class AbstractCustomerSegment extends \Pimcore\Model\Object\Concrete implements CustomerSegmentInterface
{

    public function getDataForWebserviceExport()
    {
        $data = ObjectToArray::getInstance()->toArray($this);

        if ($data['group']) {
            $data['group'] = $data['group']['id'];
        }

        return $data;
    }
}