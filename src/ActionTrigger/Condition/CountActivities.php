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
use Pimcore;
use Pimcore\Model\DataObject\Service;

class CountActivities extends AbstractMatchCondition
{
    const OPTION_TYPE = 'type';
    const OPTION_COUNT = 'count';
    const OPTION_OPERATOR = 'operator';

    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $conditionDefinition->getOptions();

        $countActivities = Pimcore::getContainer()->get('cmf.activity_store')->countActivitiesOfCustomer(
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
        $count = $options[self::OPTION_COUNT];

        return $this->matchCondition($countActivities, $operator, $count);
    }

    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {
        $options = $conditionDefinition->getOptions();

        $operator = $options[self::OPTION_OPERATOR];
        $type = $options[self::OPTION_TYPE];
        $count = intval($options[self::OPTION_COUNT]);

        $ids = Pimcore::getContainer()->get('cmf.activity_store')->getCustomerIdsMatchingActivitiesCount(
            $operator,
            $type,
            $count
        );

        if (!sizeof($ids)) {
            return '-1';
        }
        $idField = Service::getVersionDependentDatabaseColumnName('id');

        return $idField . ' in ('.implode(',', $ids).')';
    }
}
