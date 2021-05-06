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

namespace CustomerManagementFrameworkBundle\ActivityManager;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor\SegmentBuilderExecutorInterface;

class DefaultActivityManager implements ActivityManagerInterface
{
    /**
     * @var ActivityStoreInterface
     */
    protected $activityStore;

    /**
     * @var bool
     */
    protected $disableEvents = false;

    public function __construct(ActivityStoreInterface $activityStore)
    {
        $this->activityStore = $activityStore;
    }

    /**
     * @return bool
     */
    public function isDisableEvents()
    {
        return $this->disableEvents;
    }

    /**
     * Disable dispatching of php events.
     *
     * @param bool $disableEvents
     *
     * @return $this
     */
    public function setDisableEvents($disableEvents)
    {
        $this->disableEvents = $disableEvents;

        return $this;
    }

    /**
     * Add/update activity in activity store.
     * Each activity is only saved once. The activity will be updated if it already exists in the store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     *
     * @throws \Exception
     */
    public function trackActivity(ActivityInterface $activity)
    {
        $store = $this->activityStore;

        if (!($activity->cmfGetActivityDate() instanceof Carbon)) {
            throw new \Exception(
                get_class($activity).'::cmfGetActivityDate() needs to return a \Carbon\Carbon instance'
            );
        }

        if (!$activity->getCustomer() instanceof CustomerInterface) {
            $store->deleteActivity($activity);

            return;
        }

        \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class)->addCustomerToChangesQueue($activity->getCustomer());

        if (!$activity->cmfIsActive()) {
            $store->deleteActivity($activity);

            return;
        }

        if ($entry = $store->getEntryForActivity($activity)) {
            $store->updateActivityInStore($activity, $entry);
        } else {
            $entry = $store->insertActivityIntoStore($activity);

            if (!$this->isDisableEvents()) {
                $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\NewActivity(
                    $activity->getCustomer()
                );
                $event->setActivity($activity);
                $event->setEntry($entry);
                \Pimcore::getEventDispatcher()->dispatch($event, $event->getName());
            }
        }

        if (!$this->isDisableEvents()) {
            $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\AfterTrackActivity(
                $activity->getCustomer()
            );
            $event->setActivity($activity);
            $event->setEntry($entry);
            \Pimcore::getEventDispatcher()->dispatch($event, $event->getName());
        }
    }

    /**
     * Delete an activity from the activty store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function deleteActivity(ActivityInterface $activity)
    {
        $store = $this->activityStore;

        $store->deleteActivity($activity);
    }
}
