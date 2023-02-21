<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Targeting\EventListener;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Bundle\PersonalizationBundle\Event\Targeting\TargetingEvent;
use Pimcore\Bundle\PersonalizationBundle\Event\TargetingEvents;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ElementSegmentsListener implements EventSubscriberInterface
{
    /**
     * @var array
     */
    private $segmentAssignmentTypes;

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
        array $segmentAssignmentTypes,
        DocumentResolver $documentResolver,
        SegmentManagerInterface $segmentManager,
        SegmentTracker $segmentTracker
    ) {
        $this->segmentAssignmentTypes = $segmentAssignmentTypes;
        $this->documentResolver = $documentResolver;
        $this->segmentManager = $segmentManager;
        $this->segmentTracker = $segmentTracker;
    }

    /**
     * @inheritDoc
     *
     * @return array<string, string>
     */
    public static function getSubscribedEvents()//: array
    {
        return [
            TargetingEvents::PRE_RESOLVE => 'onTargetingPreResolve'
        ];
    }

    public function onTargetingPreResolve(TargetingEvent $event)
    {
        if ($this->isTypeConfigured('document')) {
            $this->trackDocumentSegments($event);
        }
    }

    private function isTypeConfigured(string $type, string $subType = null): bool
    {
        if (!isset($this->segmentAssignmentTypes[$type])) {
            return false;
        }

        // test a specific subtype
        if (null !== $subType) {
            return isset($this->segmentAssignmentTypes[$type][$subType])
                && true === $this->segmentAssignmentTypes[$type][$subType];
        }

        // test if any type is configured
        foreach ($this->segmentAssignmentTypes[$type] as $type => $value) {
            if ($value) {
                return true;
            }
        }

        return false;
    }

    private function trackDocumentSegments(TargetingEvent $event)
    {
        $visitorInfo = $event->getVisitorInfo();

        $document = $this->documentResolver->getDocument($visitorInfo->getRequest());
        if (!$document) {
            return;
        }

        $segments = $this->segmentManager->getSegmentsForElement($document);
        $segments = array_filter($segments, function ($segment) {
            return $segment instanceof CustomerSegmentInterface;
        });

        if (count($segments) > 0) {
            $this->segmentTracker->trackSegments($visitorInfo, $segments);
        }
    }
}
