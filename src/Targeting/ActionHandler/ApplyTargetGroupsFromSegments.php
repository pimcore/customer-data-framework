<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Targeting\ActionHandler;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Targeting\DataProvider\Customer;
use CustomerManagementFrameworkBundle\Targeting\SegmentTracker;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\Tool\Targeting\Rule;
use Pimcore\Model\Tool\Targeting\TargetGroup;
use Pimcore\Targeting\ActionHandler\ActionHandlerInterface;
use Pimcore\Targeting\ActionHandler\AssignTargetGroup;
use Pimcore\Targeting\ConditionMatcherInterface;
use Pimcore\Targeting\DataProviderDependentInterface;
use Pimcore\Targeting\Model\VisitorInfo;
use Pimcore\Targeting\DataLoaderInterface;
use Pimcore\Targeting\Storage\TargetingStorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ApplyTargetGroupsFromSegments implements ActionHandlerInterface, DataProviderDependentInterface
{

    const APPLY_TYPE_CLEANUP_AND_MERGE = "cleanup_and_merge";
    const APPLY_TYPE_CLEANUP_AND_OVERWRITE = "cleanup_and_overwrite";
    const APPLY_TYPE_ONLY_MERGE = "only_merge";

    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var SegmentTracker
     */
    private $segmentTracker;

    /**
     * @var TargetingStorageInterface
     */
    protected $storage;

    public function __construct(
        ConditionMatcherInterface $conditionMatcher,
        TargetingStorageInterface $storage,
        SegmentManagerInterface $segmentManager,
        SegmentTracker $segmentTracker,
        EventDispatcherInterface $eventDispatcher,
        DataLoaderInterface $dataLoader
    )
    {
        $this->segmentManager = $segmentManager;
        $this->segmentTracker = $segmentTracker;
        $this->dataLoader = $dataLoader;
        $this->storage = $storage;
    }

    /**
     * @inheritDoc
     */
    public function getDataProviderKeys(): array
    {
        return [Customer::PROVIDER_KEY];
    }

    /**
     * @inheritDoc
     */
    public function apply(VisitorInfo $visitorInfo, array $action, Rule $rule = null)
    {

        $this->dataLoader->loadDataFromProviders($visitorInfo, [Customer::PROVIDER_KEY]);
        /**
         * @var $customer CustomerInterface
         */
        $customer = $visitorInfo->get(Customer::PROVIDER_KEY);
        if(!$customer) {
            return;
        }


        $targetGroupsToConsider = $action['targetGroup'];

        //load ids of all target groups if no target groups are set
        if(empty($targetGroupsToConsider)) {
            $listing = new TargetGroup\Listing();
            $listing->load();
            foreach($listing->getTargetGroups() as $targetGroup) {
                $targetGroupsToConsider[] = $targetGroup->getId();
            }
        }

        $targetGroupInitSet = [];

        $targetGroupInitSet = $this->extractSegmentsAndCount($customer->getCalculatedSegments(), $targetGroupInitSet, $targetGroupsToConsider);
        $targetGroupInitSet = $this->extractSegmentsAndCount($customer->getManualSegments(), $targetGroupInitSet, $targetGroupsToConsider);


        $storageData = $this->storage->get(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_VISITOR,
            AssignTargetGroup::STORAGE_KEY,
            []
        );

        switch ($action['applyType']) {
            case self::APPLY_TYPE_CLEANUP_AND_OVERWRITE:
                $storageData = $this->cleanupAndOverwrite($visitorInfo, $storageData, $targetGroupsToConsider, $targetGroupInitSet);
                break;
            case self::APPLY_TYPE_CLEANUP_AND_MERGE:
                $storageData = $this->cleanupAndMerge($visitorInfo, $storageData, $targetGroupsToConsider, $targetGroupInitSet);
                break;
            case self::APPLY_TYPE_ONLY_MERGE:
                $storageData = $this->onlyMerge($visitorInfo, $storageData, $targetGroupsToConsider, $targetGroupInitSet);
                break;
            default:
                throw  new \Exception("Invalid apply type '" . $action['applyType'] . "'");
        }

        $this->storage->set(
            $visitorInfo,
            TargetingStorageInterface::SCOPE_VISITOR,
            AssignTargetGroup::STORAGE_KEY,
            $storageData
        );

    }

    protected function extractSegmentsAndCount(array $segmentArray, array $targetGroupInitSet, array $targetGroupsToConsider): array {

        foreach($segmentArray as $segment) {

            $segmentObject = null;
            $segmentAssignmentCount = null;

            if ($segment instanceof CustomerSegmentInterface) {
                $segmentObject = $segment;
                $segmentAssignmentCount = 1;
            } elseif ($segment instanceof ObjectMetadata && $segment->getObject() instanceof CustomerSegmentInterface) {
                $segmentObject = $segment->getObject();
                $segmentAssignmentCount = (int) $segment->getApplication_counter() > 0 ? (int) $segment->getApplication_counter() : 1;
            }

            if($segmentObject && $segmentObject->getUseAsTargetGroup() && $segmentObject->getTargetGroup()) {

                $targetGroupId = $segmentObject->getTargetGroup();

                if(in_array($targetGroupId, $targetGroupsToConsider)) {
                    $targetGroupInitSet[$targetGroupId] += $segmentAssignmentCount;
                }

            }

        }

        return $targetGroupInitSet;

    }

    protected function cleanupAndOverwrite(VisitorInfo $visitorInfo, array $storageData, array $consideredTargetGroupIds, array $targetGroupsToAssign): array
    {
        //clean up data
        foreach($consideredTargetGroupIds as $targetGroupId) {
            $targetGroup = TargetGroup::getById($targetGroupId);
            unset($storageData[$targetGroupId]);
            $visitorInfo->clearAssignedTargetGroup($targetGroup);
        }

        foreach($targetGroupsToAssign as $targetGroupId => $count) {
            $targetGroup = TargetGroup::getById($targetGroupId);

            if($targetGroup && $targetGroup->getActive()) {
                $storageData[$targetGroup->getId()] = $count;
                $visitorInfo->assignTargetGroup($targetGroup, $count, true);
            }

        }

        return $storageData;
    }

    protected function cleanupAndMerge(VisitorInfo $visitorInfo, array $storageData, array $consideredTargetGroupIds, array $targetGroupsToAssign): array
    {
        //clean up data
        foreach($consideredTargetGroupIds as $targetGroupId) {

            //only clean up when target group is not to be set
            if(in_array($targetGroupId, $consideredTargetGroupIds) && !isset($targetGroupsToAssign[$targetGroupId])) {
                $targetGroup = TargetGroup::getById($targetGroupId);
                unset($storageData[$targetGroupId]);
                $visitorInfo->clearAssignedTargetGroup($targetGroup);
            }

        }

        foreach($targetGroupsToAssign as $targetGroupId => $count) {
            $targetGroup = TargetGroup::getById($targetGroupId);

            if($targetGroup && $targetGroup->getActive()) {
                $oldCount = $storageData[$targetGroup->getId()];

                //only update count if new count is higher
                if($oldCount < $count) {
                    $storageData[$targetGroup->getId()] = $count;
                    $visitorInfo->assignTargetGroup($targetGroup, $count, true);
                }
            }

        }

        return $storageData;
    }


    protected function onlyMerge(VisitorInfo $visitorInfo, array $storageData, array $consideredTargetGroupIds, array $targetGroupsToAssign): array
    {
        foreach($targetGroupsToAssign as $targetGroupId => $count) {
            $targetGroup = TargetGroup::getById($targetGroupId);

            if($targetGroup && $targetGroup->getActive()) {
                $oldCount = $storageData[$targetGroup->getId()];

                //only update count if new count is higher
                if($oldCount < $count) {
                    $storageData[$targetGroup->getId()] = $count;
                    $visitorInfo->assignTargetGroup($targetGroup, $count, true);
                }
            }

        }

        return $storageData;
    }

}
