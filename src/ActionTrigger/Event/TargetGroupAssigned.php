<?php

declare(strict_types=1);

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class TargetGroupAssigned extends AbstractSingleCustomerEvent implements RuleEnvironmentAwareEventInterface
{
    const EVENT_NAME = 'plugin.cmf.target-group-assigned';

    const STORAGE_KEY = 'target_group_assigned';

    const ASSIGNMENT_TYPE_DOCUMENT = 'documents';

    const ASSIGNMENT_TYPE_TARGETING_RULE = 'targetingRules';

    /**
     * @var string
     */
    private $assignmentType;

    /**
     * @var TargetGroup
     */
    private $targetGroup;

    /**
     * @var VisitorInfo
     */
    private $visitorInfo;

    public static function create(CustomerInterface $customer, $assignmentType, TargetGroup $targetGroup, VisitorInfo $visitorInfo)
    {
        $event = new self($customer);

        $event->assignmentType = $assignmentType;
        $event->targetGroup = $targetGroup;
        $event->visitorInfo = $visitorInfo;

        return $event;
    }

    public function getAssignmentType(): string
    {
        return $this->assignmentType;
    }

    public function getTargetGroup(): TargetGroup
    {
        return $this->targetGroup;
    }

    public function getVisitorInfo(): VisitorInfo
    {
        return $this->visitorInfo;
    }

    public function getName()
    {
        return self::EVENT_NAME;
    }

    public function appliesToTrigger(TriggerDefinitionInterface $trigger)
    {
        if ($trigger->getEventName() !== $this->getName()) {
            return false;
        }

        $options = $trigger->getOptions();
        if ($options['assignmentType'] == $this->assignmentType) {
            return true;
        }

        return $options['assignmentType'] === 'all';
    }

    public function updateEnvironment(TriggerDefinitionInterface $trigger, RuleEnvironmentInterface $environment)
    {
        if ($this->visitorInfo->hasTargetGroupAssignment($this->targetGroup)) {
            $assignment = $this->visitorInfo->getTargetGroupAssignment($this->targetGroup);

            $environment->set(self::STORAGE_KEY, [
                'targetGroupId' => $this->targetGroup->getId(),
                'targetGroupWeight' => $assignment->getCount(),
            ]);
        }
    }
}
