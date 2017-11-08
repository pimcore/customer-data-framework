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
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Model\Tool\Targeting\Rule;
use Pimcore\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Targeting\Model\VisitorInfo;

class TrackSegment implements ActionHandlerInterface
{
    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var SegmentTracker
     */
    private $segmentTracker;

    public function __construct(
        SegmentManagerInterface $segmentManager,
        SegmentTracker $segmentTracker
    )
    {
        $this->segmentManager = $segmentManager;
        $this->segmentTracker = $segmentTracker;
    }

    /**
     * @inheritDoc
     */
    public function apply(VisitorInfo $visitorInfo, Rule $rule, array $action)
    {
        // TODO log errors (e.g. segment not found)

        $segmentId = $action['segmentId'];
        if (empty($segmentId)) {
            return;
        }

        $segment = $this->segmentManager->getSegmentById($segmentId);
        if (!$segment instanceof CustomerSegmentInterface) {
            return;
        }

        $this->segmentTracker->trackSegment($segment);
    }
}
