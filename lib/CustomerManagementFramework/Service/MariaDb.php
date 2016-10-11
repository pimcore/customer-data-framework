<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\Service;

use CustomerManagementFramework\Model\IActivity;
use CustomerManagementFramework\Model\ICustomer;
use Pimcore\Db;

class MariaDb {


    const ACTIVITIES_TABLE = 'plugin_cmf_activities';

    public function insertActivityIntoDb(IActivity $activity) {

        $db = Db::get();

        /* $db->insert(self::ACTIVITIES_TABLE, [
             'customerId' => $activity->getCustomer()->getId(),
             'attributes' => "COLUMN_CREATE('productClass',".$activity->getProductClass().")",
             'type' => $activity->cmfGetType(),
             'activityDate' => $activity->cmfGetActivityDate()->getTimestamp(),
             'creationDate' => time(),
             'modificationDate' => time(),
         ]);*/

        /*$data = [
            $activity->cmfGetActivityDate()->getTimestamp(),
            $activity->cmfToArray()
        ];*/


        $time = time();
        $sql = "INSERT INTO " . self::ACTIVITIES_TABLE . " (customerId, attributes, `type`, activityDate, creationDate, modificationDate) values 
        ({$activity->getCustomer()->getId()}, COLUMN_CREATE('productClass','" . $activity->getProductClass()
            . "'), '{$activity->cmfGetType()}', " . $activity->cmfGetActivityDate()->getTimestamp() . ", {$time}, {$time})";


        $db->query($sql);
    }
}