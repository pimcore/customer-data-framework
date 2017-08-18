<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
