<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFramework\Model;

use CustomerManagementFramework\Service\ObjectToArray;
use Pimcore\Model\Object\CustomerSegment;

abstract class AbstractCustomer extends \Pimcore\Model\Object\Concrete implements CustomerInterface{

    public function cmfToArray()
    {
        $result = ObjectToArray::getInstance()->toArray($this);

        $segmentIds = [];
        foreach($this->getAllSegments() as $segment) {
            $segmentIds[] = $segment->getId();
        }
        $result['segments'] = $segmentIds;

        unset($result['manualSegments']);
        unset($result['calculatedSegments']);

        return $result;
    }

    /**
     * @return CustomerSegment[]
     */
    public function getAllSegments()
    {
        return array_merge((array)$this->getCalculatedSegments(), (array)$this->getManualSegments());
    }
}
