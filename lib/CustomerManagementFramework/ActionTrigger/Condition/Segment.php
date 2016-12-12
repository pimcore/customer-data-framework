<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.12.2016
 * Time: 15:34
 */

namespace CustomerManagementFramework\ActionTrigger\Condition;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Db;
use Pimcore\Model\Object\CustomerSegment;

class Segment extends AbstractCondition
{
    const OPTION_SEGMENT_ID = 'segmentId';
    const OPTION_SEGMENT = 'segment';
    const OPTION_NOT = 'not';

    public function check(ConditionDefinitionInterface $conditionDefinition, CustomerInterface $customer) {

        $options = $conditionDefinition->getOptions();

        if(isset($options[self::OPTION_SEGMENT_ID])) {
            if($segment = CustomerSegment::getById(intval($options[self::OPTION_SEGMENT_ID]))) {
                $check = Factory::getInstance()->getSegmentManager()->customerHasSegment($customer, $segment);

                if($options[self::OPTION_NOT]) {
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

        if(!$options[self::OPTION_SEGMENT_ID]) {
            return '-1';
        }

        $segmentId = intval($options[self::OPTION_SEGMENT_ID]);

        return sprintf("FIND_IN_SET(%s, manualSegments) or FIND_IN_SET(%s, calculatedSegments)", $segmentId, $segmentId);
    }

    public static function createConditionDefinitionFromEditmode($setting)
    {
        $condition = parent::createConditionDefinitionFromEditmode($setting);

        $options = $condition->getOptions();

        if(isset($options[self::OPTION_SEGMENT])) {
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

        if(isset($options['segmentId'])) {
            if($segment = CustomerSegment::getById(intval($options['segmentId']))) {
                $options['segment'] = $segment->getFullPath();
            }
        }

        $conditionDefinition->setOptions($options);

        return $conditionDefinition->toArray();
    }
}