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

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Pimcore\Targeting\Storage\TargetingStorageInterface;

/**
 * Handles storage of tracked segments to request-persistent targeting storage (e.g. session)
 */
class SegmentTracker
{
    const KEY_SEGMENTS = 'cmf_segments';

    /**
     * @var TargetingStorageInterface
     */
    private $targetingStorage;

    public function __construct(TargetingStorageInterface $targetingStorage)
    {
        $this->targetingStorage = $targetingStorage;
    }

    /**
     * @param VisitorInfo $visitorInfo
     * @param CustomerSegmentInterface $segment
     *
     * @return array Final count for assigned segment ID
     */
    public function trackSegment(VisitorInfo $visitorInfo, CustomerSegmentInterface $segment): array
    {
        return $this->trackSegments($visitorInfo, [$segment]);
    }

    /**
     * @param VisitorInfo $visitorInfo
     * @param CustomerSegmentInterface[] $segments
     *
     * @return array Final count for assigned segment IDs
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

        return $this->trackAssignments($visitorInfo, $assignments);
    }

    /**
     * Raw method to track ID to count assignments. Use trackSegment(s) if possible.
     *
     * @param VisitorInfo $visitorInfo
     * @param array $assignments Segment ID as key, count as value
     *
     * @return array Final count for assigned segment IDs
     */
    public function trackAssignments(VisitorInfo $visitorInfo, array $assignments): array
    {
        $results = [];

        $segments = $this->getAssignments($visitorInfo);
        foreach ($assignments as $segmentId => $count) {
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
            $results[$segmentId] = $segments[$segmentId];
        }

        $this->targetingStorage->set($visitorInfo, self::KEY_SEGMENTS, $segments);

        return $results;
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
        return $this->targetingStorage->get($visitorInfo, self::KEY_SEGMENTS, []);
    }
}
