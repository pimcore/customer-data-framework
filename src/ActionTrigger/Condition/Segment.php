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

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\CustomerSegment;

class Segment extends AbstractCondition
{
    const OPTION_SEGMENT_ID = 'segmentId';
    const OPTION_SEGMENT = 'segment';
    const OPTION_NOT = 'not';

    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $conditionDefinition->getOptions();

        if (isset($options[self::OPTION_SEGMENT_ID])) {
            if ($segment = CustomerSegment::getById(intval($options[self::OPTION_SEGMENT_ID]))) {
                $check = \Pimcore::getContainer()->get('cmf.segment_manager')->customerHasSegment($customer, $segment);

                if ($options[self::OPTION_NOT]) {
                    return !$check;
                }

                return $check;
            }
        }

        return false;
    }

    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {
        $options = $conditionDefinition->getOptions();

        if (!$options[self::OPTION_SEGMENT_ID]) {
            return '-1';
        }

        $segmentId = intval($options[self::OPTION_SEGMENT_ID]);

        $not = $options[self::OPTION_NOT];

        $condition = sprintf(
            'FIND_IN_SET(%s, manualSegments) or FIND_IN_SET(%s, calculatedSegments)',
            $segmentId,
            $segmentId
        );

        if ($not) {
            $condition = '!('.$condition.')';
        }

        return $condition;
    }

    public static function createConditionDefinitionFromEditmode($setting)
    {
        $condition = parent::createConditionDefinitionFromEditmode($setting);

        $options = $condition->getOptions();

        if (isset($options[self::OPTION_SEGMENT])) {
            $segment = CustomerSegment::getByPath($options[self::OPTION_SEGMENT]);
            $options[self::OPTION_SEGMENT_ID] = $segment->getId();
            unset($options[self::OPTION_SEGMENT]);
        }
        $condition->setOptions($options);

        return $condition;
    }

    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition)
    {
        $options = $conditionDefinition->getOptions();

        if (isset($options['segmentId'])) {
            if ($segment = CustomerSegment::getById(intval($options['segmentId']))) {
                $options['segment'] = $segment->getFullPath();
            }
        }

        $conditionDefinition->setOptions($options);

        return $conditionDefinition->toArray();
    }
}
