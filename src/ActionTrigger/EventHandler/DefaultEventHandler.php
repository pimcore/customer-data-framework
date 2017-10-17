<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\EventHandler;

use CustomerManagementFrameworkBundle\ActionTrigger\Condition\Checker;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\CustomerListEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\EventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Zend\Paginator\Paginator;

class DefaultEventHandler implements EventHandlerInterface
{
    use LoggerAware;

    private $rulesGroupedByEvents;

    public function __construct()
    {
        $rules = new Rule\Listing();
        $rules->setCondition('active = 1');
        $rules = $rules->load();

        $rulesGroupedByEvents = [];

        foreach ($rules as $rule) {
            if ($triggers = $rule->getTrigger()) {
                foreach ($triggers as $trigger) {
                    $rulesGroupedByEvents[$trigger->getEventName()][] = $rule;
                }
            }
        }

        $this->rulesGroupedByEvents = $rulesGroupedByEvents;
    }

    public function handleEvent($event)
    {
        if ($event instanceof SingleCustomerEventInterface) {
            $this->handleSingleCustomerEvent($event);
        } elseif ($event instanceof CustomerListEventInterface) {
            $this->handleCustomerListEvent($event);
        }
    }

    public function handleSingleCustomerEvent(SingleCustomerEventInterface $event)
    {
        $this->getLogger()->debug(sprintf('handle single customer event: %s', $event->getName()));

        $appliedRules = $this->getAppliedRules($event);
        foreach ($appliedRules as $rule) {
            $this->handleActionsForCustomer($rule, $event->getCustomer());
        }
    }

    public function handleCustomerListEvent(CustomerListEventInterface $event)
    {
        // var_dump($this->getAppliedRules($event, false) );
        foreach ($this->getAppliedRules($event, false) as $rule) {
            if ($conditions = $rule->getCondition()) {
                $where = Checker::getDbConditionForRule($rule);

                $listing = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();
                $listing->setCondition($where);
                $listing->setOrderKey('o_id');
                $listing->setOrder('asc');

                $paginator = new Paginator($listing);
                $paginator->setItemCountPerPage(100);

                $this->getLogger()->info(
                    sprintf('handleCustomerListEvent: found %s matching customers', $paginator->getTotalItemCount())
                );

                $totalPages = $paginator->getPages()->pageCount;
                for ($i = 1; $i <= $totalPages; $i++) {
                    $paginator->setCurrentPageNumber($i);

                    foreach ($paginator as $customer) {
                        $this->handleActionsForCustomer($rule, $customer);
                    }

                    \Pimcore::collectGarbage();
                }
            }
        }
    }

    private function handleActionsForCustomer(Rule $rule, CustomerInterface $customer)
    {
        if ($actions = $rule->getAction()) {
            foreach ($actions as $action) {
                if ($action->getActionDelay()) {
                    \Pimcore::getContainer()->get('cmf.action_trigger.queue')->addToQueue($action, $customer);
                } else {
                    \Pimcore::getContainer()->get('cmf.action_trigger.action_manager')->processAction(
                        $action,
                        $customer
                    );
                }
            }
        }
    }

    /**
     * @param EventInterface $event
     * @param bool $checkConditions
     *
     * @return Rule[]
     */
    private function getAppliedRules(EventInterface $event, $checkConditions = true)
    {
        $appliedRules = [];

        if (isset($this->rulesGroupedByEvents[$event->getName()]) && sizeof(
                $this->rulesGroupedByEvents[$event->getName()]
            )
        ) {
            $rules = $this->rulesGroupedByEvents[$event->getName()];

            foreach ($rules as $rule) {
                /**
                 * @var Rule $rule ;
                 */
                foreach ($rule->getTrigger() as $trigger) {
                    if ($event->appliesToTrigger($trigger)) {
                        if ($checkConditions) {
                            if ($this->checkConditions($rule, $event)) {
                                $appliedRules[] = $rule;
                            }
                        } else {
                            $appliedRules[] = $rule;
                        }

                        break;
                    }
                }
            }
        }

        return $appliedRules;
    }

    protected function checkConditions(Rule $rule, SingleCustomerEventInterface $event)
    {
        return Checker::checkConditionsForRuleAndEvent($rule, $event);
    }
}
