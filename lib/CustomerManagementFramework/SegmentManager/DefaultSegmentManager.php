<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:02
 */

namespace CustomerManagementFramework\SegmentManager;


use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\Customer;

class DefaultSegmentManager implements SegmentManagerInterface {
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND)
    {
        $list = new \Pimcore\Model\Object\Customer\Listing;
        $list->setUnpublished(false);

        $conditions = [];
        foreach($segmentIds as $segmentId) {
            $conditions[] = "(o_id in (select src_id from object_relations_1 where dest_id = " . $list->quote($segmentId) ."))";
        }

        if(sizeof($conditions)) {
            $list->setCondition("(" . implode(' ' . $conditionMode . ' ', $conditions)  . ")");
        }


        return $list;
    }

    public function getSegmentById($segmentId)
    {
        // TODO: Implement getSegmentById() method.
    }

    public function getSegments(array $params)
    {
        $list = CustomerSegment::getList();
        $list->setUnpublished(false);

        return $list->load();
    }


}