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

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\ActionTrigger\Event\TargetGroupAssigned;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Bundle\PersonalizationBundle\Event\Targeting\AssignDocumentTargetGroupEvent;
use Pimcore\Bundle\PersonalizationBundle\Event\Targeting\TargetingRuleEvent;
use Pimcore\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class TargetingEventListener
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    protected $dataLoader;

    protected $securityToken;

    public function __construct(EventDispatcherInterface $eventDispatcher, TokenStorageInterface $token, DataLoaderInterface $dataLoader)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->securityToken = $token;
        $this->dataLoader = $dataLoader;
    }

    public function onAssignDocumentTargetGroup(AssignDocumentTargetGroupEvent $event)
    {
        $visitorInfo = $event->getVisitorInfo();

        //get customer
        $this->dataLoader->loadDataFromProviders($visitorInfo, [Customer::PROVIDER_KEY]);
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (!$customer) {
            return;
        }

        $event = TargetGroupAssigned::create(
            $customer,
            TargetGroupAssigned::ASSIGNMENT_TYPE_DOCUMENT,
            $event->getTargetGroup(),
            $visitorInfo
        );

        $this->eventDispatcher->dispatch($event, $event->getName());
    }

    public function onPostRuleActions(TargetingRuleEvent $event)
    {
        $visitorInfo = $event->getVisitorInfo();

        //get customer
        $this->dataLoader->loadDataFromProviders($visitorInfo, [Customer::PROVIDER_KEY]);
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (!$customer) {
            return;
        }

        $rule = $event->getRule();
        foreach ($rule->getActions() as $action) {
            if ($action['type'] == 'assign_target_group') {
                $targetGroup = TargetGroup::getById($action['targetGroup']);

                if (null !== $targetGroup) {
                    $event = TargetGroupAssigned::create(
                        $customer,
                        TargetGroupAssigned::ASSIGNMENT_TYPE_TARGETING_RULE,
                        $targetGroup,
                        $visitorInfo
                    );

                    $this->eventDispatcher->dispatch($event, $event->getName());
                }
            }
        }
    }
}
