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
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\Tool\Targeting\Rule;
use Pimcore\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Targeting\DataProviderDependentInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TrackSegment implements ActionHandlerInterface, DataProviderDependentInterface
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
        SegmentTracker $segmentTracker,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->segmentManager = $segmentManager;
        $this->segmentTracker = $segmentTracker;
    }

    /**
     * @inheritDoc
     */
    public function getDataProviderKeys(): array
    {
        return [Customer::PROVIDER_KEY];
    }

    /**
     * @inheritDoc
     */
    public function apply(VisitorInfo $visitorInfo, array $action, Rule $rule = null)
    {
        $segmentOption = $action['segment'];
        if (empty($segmentOption)) {
            return;
        }

        if (is_numeric($segmentOption)) {
            $segment = $this->segmentManager->getSegmentById((int)$segmentOption);
        } else {
            // TODO load from segment manager?
            $segment = CustomerSegment::getByPath($segmentOption);
        }

        // TODO log errors (e.g. segment not found)
        if (!$segment instanceof CustomerSegmentInterface) {
            return;
        }

        $this->segmentTracker->trackSegment($visitorInfo, $segment);
    }
}
