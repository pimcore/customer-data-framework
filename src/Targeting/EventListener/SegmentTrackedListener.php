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

namespace CustomerManagementFrameworkBundle\Targeting\EventListener;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SegmentTracked;
use Pimcore\Analytics\Piwik\Tracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SegmentTrackedListener implements EventSubscriberInterface
{
    /**
     * @var Tracker
     */
    private $piwikTracker;

    public function __construct(Tracker $piwikTracker)
    {
        $this->piwikTracker = $piwikTracker;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SegmentTracked::EVENT_NAME => 'onSegmentTracked'
        ];
    }

    public function onSegmentTracked(SegmentTracked $event)
    {
        $this->piwikTracker->addCodePart(sprintf(
            "_paq.push(['trackEvent', 'CMF.SegmentTracked', '%d', '%d']);",
            $event->getSegment()->getId(),
            $event->getCount()
        ));
    }
}
