<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.12.2016
 * Time: 15:34
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

class CountActivities extends AbstractCondition
{
    const OPTION_TYPE = 'type';
    const OPTION_COUNT = 'count';
    const OPTION_OPERATOR = 'operator';

    public function check(ConditionDefinitionInterface $conditionDefinition, CustomerInterface $customer)
    {

        $options = $conditionDefinition->getOptions();

        $countActivities = \Pimcore::getContainer()->get('cmf.activity_store')->countActivitiesOfCustomer(
            $customer,
            $options[self::OPTION_TYPE]
        );

        $this->logger->info(
            sprintf(
                "CountActivities condition: count activities of type '%s' for customer ID %s - result: %s",
                $options[self::OPTION_TYPE],
                $customer->getId(),
                $countActivities
            )
        );

        $operator = $options[self::OPTION_OPERATOR];

        if ($count = $options[self::OPTION_COUNT]) {

            if ($operator == ">" && ($countActivities > $count)) {
                return true;
            }

            if ($operator == "<" && ($countActivities < $count)) {
                return true;
            }

            if ($operator == "=" && ($countActivities == $count)) {
                return true;
            }

            return false;
        }

        return true;
    }

    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {

        $options = $conditionDefinition->getOptions();

        $operator = $options[self::OPTION_OPERATOR];
        $type = $options[self::OPTION_TYPE];
        $count = intval($options[self::OPTION_COUNT]);

        $ids = \Pimcore::getContainer()->get('cmf.activity_store')->getCustomerIdsMatchingActivitiesCount(
            $operator,
            $type,
            $count
        );

        if (!sizeof($ids)) {
            return "-1";
        }

        return "o_id in (".implode(',', $ids).")";
    }
}