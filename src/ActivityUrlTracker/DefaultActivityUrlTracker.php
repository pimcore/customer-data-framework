<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 23.12.2016
 * Time: 11:21
 */

namespace CustomerManagementFrameworkBundle\ActivityUrlTracker;

use CustomerManagementFrameworkBundle\Model\Activity\TrackedUrlActivity;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\Object\ActivityDefinition;

class DefaultActivityUrlTracker implements ActivityUrlTrackerInterface
{
    use LoggerAware;

    /**
     * @param $customerIdEncoded
     * @param $activityCode
     * @param array $params
     *
     * @return void
     */
    public function trackActivity($customerIdEncoded, $activityCode, array $params)
    {
        $class = \Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassName();

        if ($customer = $class::getByIdEncoded($customerIdEncoded, 1)) {

            /**
             * @var ActivityDefinition $activityDefinition
             */
            if ($activityDefinition = ActivityDefinition::getByCode($activityCode, 1)) {
                $activity = new TrackedUrlActivity($customer, $activityDefinition);

                \Pimcore::getContainer()->get('cmf.activity_manager')->trackActivity($activity);
            }
        }
    }
}
