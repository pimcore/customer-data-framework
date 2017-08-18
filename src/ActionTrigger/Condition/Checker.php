<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.12.2016
 * Time: 15:34
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;

class Checker
{
    public static function checkConditionsForRuleAndEvent(Rule $rule, SingleCustomerEventInterface $event)
    {
        $expression = '';
        $openBrackets = 0;
        if ($conditions = $rule->getCondition()) {
            foreach ($conditions as $cond) {
                $res = 'false';

                $conditionImplementation = $cond->getImplementationObject();

                if ($conditionImplementation) {
                    $res = $conditionImplementation->check($cond, $event->getCustomer());

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
