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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;

class SegmentTracked extends AbstractSingleCustomerEvent implements RuleEnvironmentAwareEventInterface
{
    const EVENT_NAME = 'plugin.cmf.segment-tracked';
    const STORAGE_KEY = 'segment_tracked';

    /**
     * @var CustomerSegmentInterface
     */
    private $segment;

    /**
     * @var int
     */
    private $count;

    public static function create(CustomerInterface $customer, CustomerSegmentInterface $segment, int $count)
    {
        $event = new self($customer);

        $event->segment = $segment;
        $event->count = $count;

        return $event;
    }

    public function getSegment(): CustomerSegmentInterface
    {
        return $this->segment;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getName()
    {
        return self::EVENT_NAME;
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if ($trigger->getEventName() === $this->getName()) {
            return true;
        }

        return false;
    }

    public function updateEnvironment(TriggerDefinitionInterface $trigger, RuleEnvironmentInterface $environment)
    {
        $environment->set(self::STORAGE_KEY, [
            'id' => $this->segment->getId(),
            'count' => $this->count
        ]);
    }
}
