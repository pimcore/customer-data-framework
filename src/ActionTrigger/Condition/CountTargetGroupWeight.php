<?php

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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\TargetGroupAssigned;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class CountTargetGroupWeight extends AbstractMatchCondition
{
    const OPTION_OPERATOR = 'operator';
    const OPTION_COUNT = 'count';
    const OPTION_TARGET_GROUP = 'targetGroup';

    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $conditionDefinition->getOptions();

        $targetGroupAssigned = $environment->get(TargetGroupAssigned::STORAGE_KEY);
        if (null === $targetGroupAssigned) {
            return false;
        }

        $targetGroupCheck = empty($options[self::OPTION_TARGET_GROUP]) || in_array($targetGroupAssigned['targetGroupId'], $options[self::OPTION_TARGET_GROUP]);

        return $targetGroupCheck && $this->matchCondition($targetGroupAssigned['targetGroupWeight'], $options[self::OPTION_OPERATOR], (int)$options[self::OPTION_COUNT]);
    }

    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {
        //return a condition that does not match any customer since this condition can only be used
        //when assigned target group trigger appeared
        return '1=2';
    }
}
