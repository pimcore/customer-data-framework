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

use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Model\Tool\Targeting\Rule;
use Pimcore\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Targeting\Model\VisitorInfo;

class TrackElementSegments implements ActionHandlerInterface
{
    /**
     * @var DocumentResolver
     */
    private $documentResolver;

    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var SegmentTracker
     */
    private $segmentTracker;

    public function __construct(
        DocumentResolver $documentResolver,
        SegmentManagerInterface $segmentManager,
        SegmentTracker $segmentTracker
    )
    {
        $this->documentResolver = $documentResolver;
        $this->segmentManager   = $segmentManager;
        $this->segmentTracker   = $segmentTracker;
    }

    /**
     * @inheritDoc
     */
    public function apply(VisitorInfo $visitorInfo, Rule $rule, array $action)
    {
        // TODO do this always as visitor info event handler or as action?

        $document = $this->documentResolver->getDocument($visitorInfo->getRequest());
        if (!$document) {
            return;
        }

        $segments = $this->segmentManager->getSegmentsForElement($document);

        $assignments = [];
        foreach ($segments as $segment) {
            $assignments[$segment->getId()] = 1;
        }

        $this->segmentTracker->trackSegments($assignments);
    }
}
