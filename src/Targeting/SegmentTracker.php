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
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Pimcore\Targeting\Session\SessionConfigurator;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Handles storage of tracked segments to session
 */
class SegmentTracker
{
    const KEY_SEGMENTS = 'cmf_segments';

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
        $request = $visitorInfo->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        /** @var NamespacedAttributeBag $bag */
        $bag = $session->getBag(SessionConfigurator::TARGETING_BAG);

        $segments = $bag->get(self::KEY_SEGMENTS, []);
        foreach ($assignments as $segmentId => $count) {
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
        }

        $bag->set(self::KEY_SEGMENTS, $segments);
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
        $request = $visitorInfo->getRequest();

        // do not read session if there was no previously started session
        if (!$request->hasPreviousSession()) {
            return [];
        }

        $session = $request->getSession();

        /** @var NamespacedAttributeBag $bag */
        $bag = $session->getBag(SessionConfigurator::TARGETING_BAG);

        return $bag->get(self::KEY_SEGMENTS, []);
    }
}
