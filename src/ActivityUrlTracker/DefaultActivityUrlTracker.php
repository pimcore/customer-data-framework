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

use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\Activity\TrackedUrlActivity;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\DataObject\LinkActivityDefinition;

class DefaultActivityUrlTracker implements ActivityUrlTrackerInterface
{
    use LoggerAware;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var ActivityManagerInterface
     */
    protected $activityManager;

    public function __construct(CustomerProviderInterface $customerProvider, ActivityManagerInterface $activityManager)
    {
        $this->customerProvider = $customerProvider;
        $this->activityManager = $activityManager;
    }

    /**
     * @param $customerIdEncoded
     * @param $activityCode
     * @param array $params
     *
     * @return void
     */
    public function trackActivity($customerIdEncoded, $activityCode, array $params)
    {
        $class = $this->customerProvider->getCustomerClassName();

        if ($customer = $class::getByIdEncoded($customerIdEncoded, 1)) {

            /**
             * @var LinkActivityDefinition $activityDefinition
             */
            if ($activityDefinition = LinkActivityDefinition::getByCode($activityCode, 1)) {

                if (!$activityDefinition->getActive()) {
                    return;
                }

                $activity = new TrackedUrlActivity($customer, $activityDefinition);

                $this->activityManager->trackActivity($activity);
            }
        }
    }
}
