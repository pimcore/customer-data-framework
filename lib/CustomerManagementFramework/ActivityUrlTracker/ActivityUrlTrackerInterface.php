<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 12.10.2016
 * Time: 13:30
 */

namespace CustomerManagementFramework\ActivityUrlTracker;

use Psr\Log\LoggerInterface;

interface ActivityUrlTrackerInterface {

    public function __construct(LoggerInterface $logger);

    public function trackActivity($customerIdEncoded, $activityCode, array $params);
}