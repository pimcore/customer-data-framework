<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFramework\ActivityManager;

use CustomerManagementFramework\Model\ActivityInterface;

interface ActivityManagerInterface
{
    /**
     * @param ActivityInterface $activity
     *
     * @return void
     */

    public function trackActivity(ActivityInterface $activity);

    /**
     * @param ActivityInterface $activity
     *
     * @return void
     */

    public function deleteActivity(ActivityInterface $activity);
}