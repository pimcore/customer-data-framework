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
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Service;

class Customer extends AbstractCondition
{
    const OPTION_CUSTOMER_ID = 'customerId';
    const OPTION_CUSTOMER = 'customer';
    const OPTION_NOT = 'not';

    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $conditionDefinition->getOptions();

        if (isset($options[self::OPTION_CUSTOMER_ID])) {
            if ($desiredCustomer = AbstractObject::getById(intval($options[self::OPTION_CUSTOMER_ID]))) {
                $check = $desiredCustomer->getId() == $customer->getId();

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

        if (!$options[self::OPTION_CUSTOMER_ID]) {
            return '-1';
        }

        $customerId = intval($options[self::OPTION_CUSTOMER_ID]);

        $idField = Service::getVersionDependentDatabaseColumnName('id');
        $condition = sprintf($idField . ' = %s', $customerId);

        $not = $options[self::OPTION_NOT];

        if ($not) {
            $condition = '!('.$condition.')';
        }

        return $condition;
    }

    public static function createConditionDefinitionFromEditmode($setting)
    {
        $condition = parent::createConditionDefinitionFromEditmode($setting);

        $options = $condition->getOptions();

        if (isset($options[self::OPTION_CUSTOMER])) {
            $customer = AbstractObject::getByPath($options[self::OPTION_CUSTOMER]);
            $options[self::OPTION_CUSTOMER_ID] = $customer->getId();
            unset($options[self::OPTION_CUSTOMER]);
        }
        $condition->setOptions($options);

        return $condition;
    }

    public static function getDataForEditmode(ConditionDefinitionInterface $conditionDefinition)
    {
        $options = $conditionDefinition->getOptions();

        if (isset($options[self::OPTION_CUSTOMER_ID])) {
            if ($segment = AbstractObject::getById(intval($options[self::OPTION_CUSTOMER_ID]))) {
                $options[self::OPTION_CUSTOMER] = $segment->getFullPath();
            }
        }

        $conditionDefinition->setOptions($options);

        return $conditionDefinition->toArray();
    }
}
