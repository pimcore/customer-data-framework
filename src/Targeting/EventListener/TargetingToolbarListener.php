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
use Pimcore\Bundle\PersonalizationBundle\Event\Targeting\RenderToolbarEvent;
use Pimcore\Bundle\PersonalizationBundle\Event\TargetingEvents;
use Pimcore\Bundle\PersonalizationBundle\Targeting\VisitorInfoStorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TargetingToolbarListener implements EventSubscriberInterface
{
    /**
     * @var VisitorInfoStorageInterface
     */
    private $visitorInfoStorage;

    /**
     * @var SegmentTracker
     */
    private $segmentTracker;

    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    public function __construct(
        VisitorInfoStorageInterface $visitorInfoStorage,
        SegmentTracker $segmentTracker,
        SegmentManagerInterface $segmentManager
    ) {
        $this->visitorInfoStorage = $visitorInfoStorage;
        $this->segmentTracker = $segmentTracker;
        $this->segmentManager = $segmentManager;
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents()//: array
    {
        return [
            TargetingEvents::RENDER_TOOLBAR => 'onRenderToolbar'
        ];
    }

    public function onRenderToolbar(RenderToolbarEvent $event)
    {
        // set the currently used template as variable scoped to cmf to
        // make sure multiple template inheritance works when another bundle
        // extends the template as well. if we'd just extend the core toolbar
        // template, any other bundles setting the template from inheritance
        // would be lost
        $event->setData(array_merge($event->getData(), [
            'cmfOriginalTemplate' => $event->getTemplate(),
            'cmfTrackedSegments' => $this->getTrackedSegments(),
        ]));

        $event->setTemplate('@PimcoreCustomerManagementFramework/targeting/toolbar.html.twig');
    }

    private function getTrackedSegments(): array
    {
        if (!$this->visitorInfoStorage->hasVisitorInfo()) {
            return [];
        }

        $visitorInfo = $this->visitorInfoStorage->getVisitorInfo();
        $trackedSegments = $this->segmentTracker->getAssignments($visitorInfo);

        $result = [];
        foreach ($trackedSegments as $id => $count) {
            $segment = $this->segmentManager->getSegmentById($id);
            if (!$segment || !$segment instanceof CustomerSegmentInterface) {
                continue;
            }

            $result[] = [
                'id' => $segment->getId(),
                'name' => $segment->getName(),
                'group' => $segment->getGroup() ? $segment->getGroup()->getName() : null,
                'calculated' => $segment->getCalculated(),
                'count' => $count,
            ];
        }

        return $result;
    }
}
