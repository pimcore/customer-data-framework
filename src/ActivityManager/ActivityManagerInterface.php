<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace  CustomerManagementFrameworkBundle\ActivityManager;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;

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