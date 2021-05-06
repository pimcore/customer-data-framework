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

class CustomerField extends AbstractCondition
{
    const OPTION_FIELDNAME = 'fieldname';
    const OPTION_FIELDVALUE = 'fieldvalue';
    const OPTION_NOT = 'not';

    public function check(
        ConditionDefinitionInterface $conditionDefinition,
        CustomerInterface $customer,
        RuleEnvironmentInterface $environment
    ) {
        $options = $conditionDefinition->getOptions();

        if (isset($options[self::OPTION_FIELDNAME]) && isset($options[self::OPTION_FIELDVALUE])) {
            $getter = 'get' . ucfirst($options[self::OPTION_FIELDNAME]);
            if (method_exists($customer, $getter)) {
                if (
                    ($options[self::OPTION_NOT] && $customer->$getter() != $options[self::OPTION_FIELDVALUE]) ||
                    (!$options[self::OPTION_NOT] && $customer->$getter() == $options[self::OPTION_FIELDVALUE])
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getDbCondition(ConditionDefinitionInterface $conditionDefinition)
    {
        $options = $conditionDefinition->getOptions();

        if (isset($options[self::OPTION_FIELDNAME]) && isset($options[self::OPTION_FIELDVALUE])) {
            return $options[self::OPTION_FIELDNAME] . ' ' . ($options[self::OPTION_NOT] ? '!' : '') . '= "' . str_replace('"', '', $options[self::OPTION_FIELDVALUE]) . '"';
        }

        return '-1';
    }
}
