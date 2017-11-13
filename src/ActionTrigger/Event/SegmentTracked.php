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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;

class SegmentTracked extends AbstractSingleCustomerEvent
{
    /**
     * @var CustomerSegmentInterface
     */
    private $segment;

    public static function create(CustomerInterface $customer, CustomerSegmentInterface $segment)
    {
        $event = new self($customer);
        $event->segment = $segment;

        return $event;
    }

    public function getSegment(): CustomerSegmentInterface
    {
        return $this->segment;
    }

    public function getName()
    {
        return 'plugin.cmf.segment-tracked';
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if ($trigger->getEventName() !== $this->getName()) {
            return false;
        }

        $configuredSegmentId = $trigger->getOptions()['segmentId'] ?? null;
        if (!$configuredSegmentId) {
            return false;
        }

        return $configuredSegmentId === $this->segment->getId();
    }
}
