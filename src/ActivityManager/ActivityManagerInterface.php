<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:22
 */

namespace CustomerManagementFrameworkBundle\ActivityManager;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;

interface ActivityManagerInterface
{
    /**
     * Add/update activity in activity store.
     * Each activity is only saved once. The activity will be updated if it already exists in the store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function trackActivity(ActivityInterface $activity);

    /**
     * Delete activity from activity store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function deleteActivity(ActivityInterface $activity);
}
