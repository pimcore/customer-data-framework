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

class CountActivities extends AbstractCondition
{
    const OPTION_TYPE = 'type';
    const OPTION_COUNT = 'count';

    public function check(ConditionDefinitionInterface $conditionDefinition, CustomerInterface $customer) {

        $options = $conditionDefinition->getOptions();

        $countActivities = Factory::getInstance()->getActivityStore()->countActivitiesOfCustomer($customer, $options[self::OPTION_TYPE]);

        $this->logger->debug(sprintf("CountActivities condition: count activities of type '%s' for customer ID %s - result: %s", $options[self::OPTION_TYPE], $customer->getId(), $countActivities));

        if($count = $options[self::OPTION_COUNT]) {
            if($count <= $countActivities) {
                return true;
            }

            return false;
        }

        return true;
    }
}