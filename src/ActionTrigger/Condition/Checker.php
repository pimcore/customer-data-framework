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

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;

class Checker
{
    public static function checkConditionsForRuleAndEvent(
        Rule $rule,
        SingleCustomerEventInterface $event,
        RuleEnvironmentInterface $environment
    ) {
        $expression = '';
        $openBrackets = 0;
        if ($conditions = $rule->getCondition()) {
            foreach ($conditions as $cond) {
                $res = 'false';

                $conditionImplementation = $cond->getImplementationObject();

                if ($conditionImplementation) {
                    $res = $conditionImplementation->check($cond, $event->getCustomer(), $environment);

                    if ($res) {
                        $res = 'true';
                    } else {
                        $res = 'false';
                    }
                }

                if ($expression) {
                    $operator = $cond->getOperator();

                    if ($operator == 'and') {
                        $expression .= ' && ';
                    } elseif ($operator == 'or') {
                        $expression .= ' || ';
                    } elseif ($operator == 'and_not') {
                        $expression .= ' && !';
                    }
                }

                if ($cond->getBracketLeft()) {
                    $expression .= '(';
                    $openBrackets++;
                }

                $expression .= $res;

                if ($openBrackets && $cond->getBracketRight()) {
                    $expression .= ')';
                    $openBrackets--;
                }
            }
        }

        for ($i = 0; $i < $openBrackets; $i++) {
            $expression .= ')';
        }

        if (!$expression) {
            return true;
        }

        // don't be afraid of this eval - it's save!
        return eval('return ('.$expression.');');
    }

    public static function getDbConditionForRule(Rule $rule)
    {
        $expression = '';
        $openBrackets = 0;
        if ($conditions = $rule->getCondition()) {
            foreach ($conditions as $cond) {
                $res = 'false';

                $conditionImplementation = $cond->getImplementationObject();

                if ($conditionImplementation) {
                    $res = $conditionImplementation->getDbCondition($cond);

                    $res = '('.$res.')';
                }

                if ($expression) {
                    $operator = $cond->getOperator();

                    if ($operator == 'and') {
                        $expression .= ' and ';
                    } elseif ($operator == 'or') {
                        $expression .= ' or ';
                    } elseif ($operator == 'and_not') {
                        $expression .= ' and not ';
                    }
                }

                if ($cond->getBracketLeft()) {
                    $expression .= '(';
                    $openBrackets++;
                }

                $expression .= $res;

                if ($openBrackets && $cond->getBracketRight()) {
                    $expression .= ')';
                    $openBrackets--;
                }
            }
        }

        for ($i = 0; $i < $openBrackets; $i++) {
            $expression .= ')';
        }

        if (!$expression) {
            return '1';
        }

        return $expression;
    }
}
