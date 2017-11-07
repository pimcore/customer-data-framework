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

use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Model\Tool\Targeting\Rule;
use Pimcore\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Targeting\Model\VisitorInfo;

class TrackSegment implements ActionHandlerInterface
{
    /**
     * @var SegmentTracker
     */
    private $segmentTracker;

    public function __construct(SegmentTracker $segmentTracker)
    {
        $this->segmentTracker = $segmentTracker;
    }

    /**
     * @inheritDoc
     */
    public function apply(VisitorInfo $visitorInfo, Rule\Actions $actions, Rule $rule)
    {
        // TODO enable only if configured
        // TODO make configurable and assign configured segment
    }
}
