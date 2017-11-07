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
use Pimcore\Targeting\Session\SessionConfigurator;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SegmentTracker
{
    const KEY_SEGMENTS = 'cmf_segments';

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param CustomerSegmentInterface|int $segment
     * @param int $count
     */
    public function trackSegment($segment, int $count = 1)
    {
        if ($segment instanceof CustomerSegmentInterface) {
            $segment = $segment->getId();
        }

        $this->trackSegments([
            $segment => $count
        ]);
    }

    /**
     * @param array $assignments Segment ID as key, count as value
     */
    public function trackSegments(array $assignments)
    {
        /** @var NamespacedAttributeBag $bag */
        $bag = $this->session->getBag(SessionConfigurator::TARGETING_BAG);

        $segments = $bag->get(self::KEY_SEGMENTS, []);
        foreach ($assignments as $segmentId => $count) {
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
        }

        $bag->set(self::KEY_SEGMENTS, $segments);
    }
}
