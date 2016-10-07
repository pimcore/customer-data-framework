<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFramework\ActivityManager;

use CustomerManagementFramework\Model\IActivity;
use Pimcore\Db;

class DefaultActivityManager implements IActivityManager
{
    const ACTIVITIES_TABLE = 'plugin_cmf_activities';

    /**
     * @param IActivity $activity
     *
     * @return void
     */
    
    public function trackActivity(IActivity $activity) {

        $db = Db::get();

        $db->insert(self::ACTIVITIES_TABLE, [
            'customerId' => $activity->getCustomer()->getId(),
            'attributes' => $activity->cmfToArray(),
            'type' => $activity->cmfGetType(),
            'activityDate' => $activity->cmfGetActivityDate()->getTimestamp(),
            'creationDate' => time(),
            'modificationDate' => time(),
        ]);

        $id = $db->lastInsertId();

        $activity->addCmfActivityId($id);
        $activity->save();
    }
}