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

namespace CustomerManagementFrameworkBundle\ActionTrigger\EventHandler;

use CustomerManagementFrameworkBundle\ActionTrigger\Condition\Checker;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\CustomerListEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\EventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\RuleEnvironmentAwareEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Event\SingleCustomerEventInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Queue\QueueInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironment;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironmentInterface;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore;
use Pimcore\Model\DataObject\Service;

class DefaultEventHandler implements EventHandlerInterface
{
    use LoggerAware;

    /** @var Rule[][]|null */
    private $rulesGroupedByEvents = null;

    /**
     * @var QueueInterface
     */
    protected $actionTriggerQueue;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    public function __construct(QueueInterface $actionTriggerQueue, PaginatorInterface $paginator)
    {
        $this->actionTriggerQueue = $actionTriggerQueue;
        $this->paginator = $paginator;
    }

    protected function getRulesGroupedByEvents()
    {
        if ($this->rulesGroupedByEvents === null) {
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

        return $this->rulesGroupedByEvents;
    }

    public function handleEvent($event)
    {
        $environment = new RuleEnvironment();

        if ($event instanceof SingleCustomerEventInterface) {
            $this->handleSingleCustomerEvent($event, $environment);
        } elseif ($event instanceof CustomerListEventInterface) {
            $this->handleCustomerListEvent($event, $environment);
        }
    }

    public function handleSingleCustomerEvent(SingleCustomerEventInterface $event, RuleEnvironmentInterface $environment)
    {
        $this->getLogger()->debug(sprintf('handle single customer event: %s', $event->getName()));

        $appliedRules = $this->getAppliedRules($event, $environment, true);
        foreach ($appliedRules as $rule) {
            $this->handleActionsForCustomer($rule, $event->getCustomer(), $environment);
        }
    }

    public function handleCustomerListEvent(CustomerListEventInterface $event, RuleEnvironmentInterface $environment)
    {
        foreach ($this->getAppliedRules($event, $environment, false) as $rule) {
            if ($conditions = $rule->getCondition()) {
                $where = Checker::getDbConditionForRule($rule);

                $listing = Pimcore::getContainer()->get('cmf.customer_provider')->getList();
                $listing->setCondition($where);
                $idField = Service::getVersionDependentDatabaseColumnName('id');
                $listing->setOrderKey($idField);
                $listing->setOrder('asc');

                $paginator = $this->paginator->paginate($listing, 1, 100);

                $this->getLogger()->info(
                    sprintf('handleCustomerListEvent: found %s matching customers', $paginator->getTotalItemCount())
                );

                $totalPages = $paginator->getPaginationData()['totalCount'];
                for ($i = 1; $i <= $totalPages; $i++) {
                    $paginator = $this->paginator->paginate($listing, $i, 100);

                    foreach ($paginator as $customer) {
                        $this->handleActionsForCustomer($rule, $customer, $environment);
                    }

                    Pimcore::collectGarbage();
                }
            }
        }
    }

    private function handleActionsForCustomer(Rule $rule, CustomerInterface $customer, RuleEnvironmentInterface $environment)
    {
        if ($actions = $rule->getAction()) {
            foreach ($actions as $action) {
                if ($action->getActionDelay()) {
                    $this->actionTriggerQueue->addToQueue(
                        $action,
                        $customer,
                        $environment
                    );
                } else {
                    Pimcore::getContainer()->get('cmf.action_trigger.action_manager')->processAction(
                        $action,
                        $customer,
                        $environment
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
    private function getAppliedRules(EventInterface $event, RuleEnvironmentInterface $environment, $checkConditions = true)
    {
        $appliedRules = [];

        if (isset($this->getRulesGroupedByEvents()[$event->getName()]) && sizeof(
                $this->getRulesGroupedByEvents()[$event->getName()]
            )
        ) {
            $rules = $this->rulesGroupedByEvents[$event->getName()];

            foreach ($rules as $rule) {
                foreach ($rule->getTrigger() as $trigger) {
                    if ($event->appliesToTrigger($trigger)) {
                        if ($event instanceof RuleEnvironmentAwareEventInterface) {
                            $event->updateEnvironment($trigger, $environment);
                        }

                        if ($checkConditions && $event instanceof SingleCustomerEventInterface) {
                            if ($this->checkConditions($rule, $event, $environment)) {
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

    protected function checkConditions(Rule $rule, SingleCustomerEventInterface $event, RuleEnvironmentInterface $environment)
    {
        return Checker::checkConditionsForRuleAndEvent($rule, $event, $environment);
    }
}
