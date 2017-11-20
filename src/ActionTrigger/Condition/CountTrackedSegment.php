<?php

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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SegmentTracked;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;

class CountTrackedSegment extends AbstractCondition
{
    const OPTION_OPERATOR = 'operator';
    const OPTION_COUNT = 'count';

    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    )
    {
        $options = $conditionDefinition->getOptions();

        $trackedSegment = $environment->get(SegmentTracked::STORAGE_KEY);
        if (null === $trackedSegment) {
            return false;
        }

        $segmentManager = \Pimcore::getContainer()->get('cmf.segment_manager');

        $segment = $segmentManager->getSegmentById($trackedSegment['id'] ?? null);
        if (!$segment instanceof CustomerSegmentInterface) {
            return false;
        }

        $trackedCount = $trackedSegment['count'] ?? null;
        if (null === $trackedCount) {
            return false;
        }

        return $this->matchCondition($trackedCount, $options[self::OPTION_OPERATOR], (int)$options[self::OPTION_COUNT]);
    }

    private function matchCondition(int $segmentCount, string $operator, int $value): bool
    {
        switch ($operator) {
            case '%':
                return $segmentCount % $value === 0;

            case '=':
                return $segmentCount === $value;

            case '>':
                return $segmentCount > $value;

            case '>=':
                return $segmentCount >= $value;

            case '<':
                return $segmentCount < $value;

            case '<=':
                return $segmentCount <= $value;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported operator "%s"', $operator));
    }

    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {
        return ''; // TODO what do to here?
    }
}
