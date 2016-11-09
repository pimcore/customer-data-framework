<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFramework\ActivityManager;

use CustomerManagementFramework\Model\IActivity;
use CustomerManagementFramework\Model\ICustomer;

interface IActivityManager
{
    /**
     * @param IActivity $activity
     *
     * @return void
     */

    public function trackActivity(IActivity $activity);

    /**
     * @param IActivity $activity
     *
     * @return void
     */

    public function deleteActivity(IActivity $activity);
}