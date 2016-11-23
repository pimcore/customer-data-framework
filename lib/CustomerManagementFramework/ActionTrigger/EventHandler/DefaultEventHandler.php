<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 12:43
 */

namespace CustomerManagementFramework\ActionTrigger\EventHandler;


use CustomerManagementFramework\ActionTrigger\Event\EventInterface;
use CustomerManagementFramework\ActionTrigger\Rule;
use CustomerManagementFramework\Factory;

class DefaultEventHandler implements EventHandlerInterface{

    private $rulesGroupedByEvents;

    public function __construct()
    {
        $rules = new Rule\Listing();
        $rules = $rules->load();

        $rulesGroupedByEvents = [];

        foreach($rules as $rule) {
            if($triggers = $rule->getTrigger()) {
                foreach($triggers as $trigger) {
                    $rulesGroupedByEvents[$trigger->getEventName()][] = $rule;
                }
            }
        }

        $this->rulesGroupedByEvents = $rulesGroupedByEvents;
    }

    public function handleEvent(\Zend_EventManager_Event $e, EventInterface $event)
    {

        $appliedRules = $this->getAppliedRules($event);

        foreach($appliedRules as $rule) {
            if($rule->getActionDelay()) {
                $this->addToQueue($rule, $event);
            }
        }
    }

    /**
     * @param EventInterface $event
     *
     * @return Rule[]
     */
    private function getAppliedRules(EventInterface $event) {

        $appliedRules = [];

        if(isset($this->rulesGroupedByEvents[$event->getName()]) && sizeof($this->rulesGroupedByEvents[$event->getName()])) {

            $rules = $this->rulesGroupedByEvents[$event->getName()];

            foreach($rules as $rule) {
                /**
                 * @var Rule $rule;
                 */

                foreach($rule->getTrigger() as $trigger) {
                    if($event->appliesToTrigger($trigger)) {
                        $appliedRules[] = $rule;
                        break;
                    }
                }
            }
        }

        return $appliedRules;
    }

    private function addToQueue(Rule $rule, EventInterface $event)
    {
        Factory::getInstance()->getActionTriggerQueue()->addToQueue($rule, $event);
    }
}