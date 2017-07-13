<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 12.10.2016
 * Time: 13:30
 */

namespace CustomerManagementFrameworkBundle\ActivityUrlTracker;

interface ActivityUrlTrackerInterface
{
    /**
     * @param $customerIdEncoded
     * @param $activityCode
     * @param array $params
     * @return void
     */
    public function trackActivity($customerIdEncoded, $activityCode, array $params);
}