<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Targeting;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SegmentTracked;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Targeting\DataLoaderInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Pimcore\Targeting\Storage\TargetingStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles storage of tracked segments to request-persistent targeting storage (e.g. session)
 */
class SegmentTracker
{
    const KEY_SEGMENTS = 'cmf:sg';

    /**
     * @var TargetingStorageInterface
     */
    private $targetingStorage;

    /**
     * @var DataLoaderInterface
     */
    private $dataLoader;

    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        TargetingStorageInterface $targetingStorage,
        DataLoaderInterface $dataLoader,
        SegmentManagerInterface $segmentManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->targetingStorage = $targetingStorage;
        $this->dataLoader       = $dataLoader;
        $this->segmentManager   = $segmentManager;
        $this->eventDispatcher  = $eventDispatcher;
    }

    public function trackSegment(VisitorInfo $visitorInfo, CustomerSegmentInterface $segment)
    {
        $this->trackSegments($visitorInfo, [$segment]);
    }

    /**
     * @param VisitorInfo $visitorInfo
     * @param CustomerSegmentInterface[] $segments
     */
    public function trackSegments(VisitorInfo $visitorInfo, array $segments)
    {
        $assignments = [];
        foreach ($segments as $segment) {
            if (!$segment instanceof CustomerSegmentInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Segments is expected to be an array of CustomerSegmentInterface instances, but got a %s',
                    is_object($segment) ? get_class($segment) : gettype($segment)
                ));
            }

            $assignments[$segment->getId()] = 1;
        }

        $this->trackAssignments($visitorInfo, $assignments);
    }

    /**
     * Raw method to track ID to count assignments. Use trackSegment(s) if possible.
     *
     * @param VisitorInfo $visitorInfo
     * @param array $assignments Segment ID as key, count as value
     */
    public function trackAssignments(VisitorInfo $visitorInfo, array $assignments)
    {
        $eventData = [];

        $segments = $this->getAssignments($visitorInfo);
        foreach ($assignments as $segmentId => $count) {
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
            $eventData[$segmentId] = $segments[$segmentId];
        }

        $this->targetingStorage->set(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_VISITOR,
            self::KEY_SEGMENTS,
            $segments
        );

        foreach ($eventData as $segmentId => $count) {
            $this->dispatchTrackEvent($visitorInfo, $segmentId, $count);
        }
    }

    /**
     * Read ID <-> count assignment mapping from storage
     *
     * @param VisitorInfo $visitorInfo
     *
     * @return array
     */
    public function getAssignments(VisitorInfo $visitorInfo): array
    {
        return $this->targetingStorage->get(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_VISITOR,
            self::KEY_SEGMENTS,
            []
        );
    }

    private function dispatchTrackEvent(VisitorInfo $visitorInfo, int $segmentId, int $count)
    {
        $this->dataLoader->loadDataFromProviders($visitorInfo, [Customer::PROVIDER_KEY]);

        /** @var CustomerInterface $customer */
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (null === $customer) {
            return;
        }

        $segment = $this->segmentManager->getSegmentById($segmentId);
        if (null === $segment) {
            return;
        }

        $event = SegmentTracked::create($customer, $segment, $count);

        $this->eventDispatcher->dispatch($event->getName(), $event);
    }
}
