<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.12.2016
 * Time: 15:34
 */

namespace CustomerManagementFramework\ActionTrigger\Condition;


use CustomerManagementFramework\ActionTrigger\Event\EventInterface;
use CustomerManagementFramework\ActionTrigger\Rule;

class Checker
{
    public static function checkConditionsForRuleAndEvent(Rule $rule, EventInterface $event)
    {

        $expression = '';
        $openBrackets = 0;
        if($conditions = $rule->getCondition()) {


            foreach($conditions as $cond) {
                $res = "false";

                $conditionImplementation = $cond->getImplementationObject();

                if($conditionImplementation) {
                    $res = $conditionImplementation->check($cond, $event->getCustomer());

                    if($res) {
                        $res = "true";
                    } else {
                        $res = "false";
                    }
                }



                if($expression) {

                    $operator = $cond->getOperator();

                    if($operator == 'and') {
                        $expression .= ' && ';
                    } elseif($operator == 'or') {
                        $expression .= ' || ';
                    } elseif($operator == 'and_not') {
                        $expression .= ' && !';
                    }
                }

                if($cond->getBracketLeft()) {
                    $expression .= '(';
                    $openBrackets++;
                }

                $expression .= $res;

                if($openBrackets && $cond->getBracketRight()) {
                    $expression .= ')';
                    $openBrackets--;
                }
            }
        }

        for($i=0;$i<$openBrackets;$i++) {
            $expression .= ')';
        }

        if(!$expression) {
            return true;
        }


        return eval('return ('.$expression.');');

    }
}