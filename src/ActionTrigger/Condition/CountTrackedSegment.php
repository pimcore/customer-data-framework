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

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SegmentTracked;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;

class CountTrackedSegment extends AbstractMatchCondition
{
    const OPTION_OPERATOR = 'operator';
    const OPTION_COUNT = 'count';
    const OPTION_SEGMENTS = 'segments';

    /**
     * @inheritdoc
     */
    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
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

        if (!empty($options[self::OPTION_SEGMENTS]) && !in_array($trackedSegment['id'], $options[self::OPTION_SEGMENTS])) {
            return false;
        }

        $trackedCount = $trackedSegment['count'] ?? null;
        if (null === $trackedCount) {
            return false;
        }

        return $this->matchCondition($trackedCount, $options[self::OPTION_OPERATOR], (int)$options[self::OPTION_COUNT]);
    }

    /**
     * @inheritdoc
     */
    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {
        //return a condition that does not match any customer since this condition can only be used
        //when assigned target group trigger appeared
        return '1=2';
    }

    /**
     * @inheritdoc
     */
    public static function createConditionDefinitionFromEditmode($setting)
    {
        $segmentDataArray = $setting->options->segments;
        $segmentIds = [];
        if ($segmentDataArray) {
            foreach ($segmentDataArray as $segmentData) {
                $segmentIds[] = $segmentData->id;
            }
        }

        $setting->options->segments = $segmentIds;
        $setting = json_decode(json_encode($setting), true);

        return new \CustomerManagementFrameworkBundle\Model\ActionTrigger\ConditionDefinition($setting);
    }

    /**
     * @inheritdoc
     */
    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition)
    {
        $segmentManager = \Pimcore::getContainer()->get('cmf.segment_manager');

        $dataArray = $conditionDefinition->toArray();
        $options = $conditionDefinition->getOptions();
        $originalSegments = $options[self::OPTION_SEGMENTS];
        $dataSegments = [];
        foreach ($originalSegments as $originalSegmentData) {
            $segment = $segmentManager->getSegmentById($originalSegmentData);
            if ($segment) {
                $dataSegments[] = [
                    $segment->getId(),
                    $segment->getFullPath()
                ];
            }
        }

        $dataArray['options'][self::OPTION_SEGMENTS] = $dataSegments;

        return $dataArray;
    }
}
