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

namespace CustomerManagementFrameworkBundle\Targeting\ActionHandler;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\Tool\Targeting\Rule;
use Pimcore\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Pimcore\Targeting\Session\SessionConfigurator;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TrackSegment implements ActionHandlerInterface
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
     * @inheritDoc
     */
    public function apply(VisitorInfo $visitorInfo, Rule\Actions $actions, Rule $rule)
    {
        // TODO enable only if configured
        // TODO make configurable and assign configured segment
    }

    public function assignSegment(CustomerSegmentInterface $segment, int $count = 1)
    {
        $this->assignSegments([
            $segment->getId() => $count
        ]);
    }

    public function assignSegments(array $assignments)
    {
        $bag      = $this->getSessionBag();
        $segments = $bag->get(self::KEY_SEGMENTS, []);

        foreach ($assignments as $segmentId => $count) {
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }

            $segments[$segmentId] += $count;
        }

        $bag->set(self::KEY_SEGMENTS, $segments);
    }

    private function getSessionBag(): NamespacedAttributeBag
    {
        /** @var NamespacedAttributeBag $bag */
        $bag = $this->session->getBag(SessionConfigurator::TARGETING_BAG);

        return $bag;
    }
}
