<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Targeting\ActionHandler;

use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\GDPR\Consent\ConsentCheckerInterface;
use CustomerManagementFrameworkBundle\Model\Activity\TargetGroupAssignActivity;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use Pimcore\Bundle\PersonalizationBundle\Model\Tool\Targeting\Rule;
use Pimcore\Bundle\PersonalizationBundle\Model\Tool\Targeting\TargetGroup;
use Pimcore\Bundle\PersonalizationBundle\Targeting\ActionHandler\AssignTargetGroup;
use Pimcore\Bundle\PersonalizationBundle\Targeting\DataLoaderInterface;
use Pimcore\Bundle\PersonalizationBundle\Targeting\Model\VisitorInfo;

class AssignTargetGroupAndSegment extends AssignTargetGroup
{
    /**
     * @var SegmentManagerInterface
     */
    protected $segmentManager;

    /**
     * @var ActivityManagerInterface
     */
    protected $activityManager;

    /**
     * @var DataLoaderInterface
     */
    protected $dataLoader;

    /**
     * @var ConsentCheckerInterface
     */
    protected $consentChecker;

    /**
     * @required
     */
    public function setSegmentManager(SegmentManagerInterface $segmentManager): void
    {
        $this->segmentManager = $segmentManager;
    }

    /**
     * @required
     */
    public function setActivityManager(ActivityManagerInterface $activityManager): void
    {
        $this->activityManager = $activityManager;
    }

    /**
     * @required
     */
    public function setDataLoader(DataLoaderInterface $dataLoader): void
    {
        $this->dataLoader = $dataLoader;
    }

    /**
     * @required
     */
    public function setConsentChecker(ConsentCheckerInterface $consentChecker): void
    {
        $this->consentChecker = $consentChecker;
    }

    /**
     * @inheritdoc
     */
    public function apply(VisitorInfo $visitorInfo, array $action, Rule $rule = null): void
    {
        parent::apply($visitorInfo, $action, $rule);

        //get customer
        $this->dataLoader->loadDataFromProviders($visitorInfo, [Customer::PROVIDER_KEY]);
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if (!$customer) {
            return;
        }

        if (isset($action['considerProfilingConsent']) && $action['considerProfilingConsent'] !== false && !$this->consentChecker->hasProfilingConsent($customer)) {
            return;
        }

        $targetGroupId = $action['targetGroup'] ?? null;
        if ($targetGroupId instanceof TargetGroup) {
            $targetGroup = $targetGroupId;
            $targetGroupId = $targetGroup->getId();
        } else {
            $targetGroup = TargetGroup::getById($targetGroupId);
        }

        if (isset($action['trackActivity']) && $action['trackActivity'] && $targetGroup) {
            $totalWeight = $action['weight'];
            if ($visitorInfo->hasTargetGroupAssignment($targetGroup)) {
                $assignedTargetGroup = $visitorInfo->getTargetGroupAssignment($targetGroup);
                $totalWeight = $assignedTargetGroup->getCount();
            }

            $this->activityManager->trackActivity(new TargetGroupAssignActivity($customer, $targetGroup, $action['weight'], $totalWeight));
        }

        if (isset($action['assignSegment']) && ($action['assignSegment'] === 'assign_only' || $action['assignSegment'] === 'assign_consider_weight')) {
            //get segment based on target group
            $segments = $this->segmentManager->getSegments();
            $segments->setCondition('targetGroup = ?', $targetGroupId);
            $segments->load();

            if ($segments->getObjects()) {
                if ($action['assignSegment'] === 'assign_consider_weight') {
                    //loop needed to make sure segment is assigned weight-times
                    //strange things with timestamp are needed in order to make sure assignments have different timestamps so they count correctly
                    $timestamp = time() - $action['weight'];
                    $segmentApplicationCounter = true;

                    for ($i = 0; $i < $action['weight']; $i++) {
                        $this->segmentManager->mergeSegments(
                            $customer,
                            $segments->getObjects(),
                            [],
                            'AssignPersonGroupAndSegment action trigger action based on rule ' . $rule->getName(),
                            $timestamp + $i,
                            $segmentApplicationCounter
                        );
                    }
                } else {
                    $this->segmentManager->mergeSegments(
                        $customer,
                        $segments->getObjects(),
                        [],
                        'AssignPersonGroupAndSegment action trigger action based on rule ' . $rule->getName()
                    );
                }
            }

            $this->segmentManager->saveMergedSegments($customer);
        }
    }
}
