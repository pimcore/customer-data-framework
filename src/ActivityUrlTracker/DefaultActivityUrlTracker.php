<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.12.2016
 * Time: 11:21
 */

namespace CustomerManagementFrameworkBundle\ActivityUrlTracker;

use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\Activity\TrackedUrlActivity;
use Pimcore\Model\Object\ActivityDefinition;
use Psr\Log\LoggerInterface;

class DefaultActivityUrlTracker implements ActivityUrlTrackerInterface
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function trackActivity($customerIdEncoded, $activityCode, array $params)
    {
        $class = \Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassName();

        if($customer = $class::getByIdEncoded($customerIdEncoded, 1)) {

            if($activityDefinition = ActivityDefinition::getByCode($activityCode, 1)) {
                $activity = new TrackedUrlActivity($customer, $activityDefinition);

                \Pimcore::getContainer()->get('cmf.activity_manager')->trackActivity($activity);
            }
        }
    }

}