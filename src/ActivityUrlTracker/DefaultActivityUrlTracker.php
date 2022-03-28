<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\ActivityUrlTracker;

use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\Activity\TrackedUrlActivity;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
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
     * @param string $customerIdEncoded
     * @param string $activityCode
     * @param array $params
     *
     * @return void
     */
    public function trackActivity($customerIdEncoded, $activityCode, array $params)
    {
        $class = $this->customerProvider->getCustomerClassName();
        /** @var CustomerInterface|null $customer */
        $customer = $class::getByIdEncoded($customerIdEncoded, 1);

        if ($customer) {
            /** @var LinkActivityDefinition|null $activityDefinition */
            $activityDefinition = LinkActivityDefinition::getByCode($activityCode, 1);
            if ($activityDefinition) {
                if (!$activityDefinition->getActive()) {
                    return;
                }

                $activity = new TrackedUrlActivity($customer, $activityDefinition);

                $this->activityManager->trackActivity($activity);
            }
        }
    }
}
