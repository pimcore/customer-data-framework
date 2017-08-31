<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\SegmentManager;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentBuilder\SegmentBuilderInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Pimcore\Model\Object\Concrete;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Pimcore\Model\Object\Service;
use Zend\Paginator\Paginator;

class DefaultSegmentManager implements SegmentManagerInterface
{
    use LoggerAware;

    const CHANGES_QUEUE_TABLE = 'plugin_cmf_segmentbuilder_changes_queue';

    protected $mergedSegmentsCustomerSaveQueue;

    protected $segmentFolderCalculated;
    protected $segmentFolderManual;

    /**
     * @var CustomerSaveManagerInterface
     */
    protected $customerSaveManager;

    /**
     * @var SegmentBuilderInterface
     */
    protected $segmentBuilders = [];

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    public function __construct($segmentFolderCalculated, $segmentFolderManual, CustomerSaveManagerInterface $customerSaveManager, CustomerProviderInterface $customerProvider)
    {
        $this->segmentFolderCalculated = $segmentFolderCalculated;
        $this->segmentFolderManual = $segmentFolderManual;

        $this->customerSaveManager = $customerSaveManager;
        $this->customerProvider = $customerProvider;
    }

    /**
     * @param int $id
     *
     * @return CustomerSegmentInterface
     */
    public function getSegmentById($id)
    {
        return CustomerSegment::getById($id);
    }

    /**
     * @param int $id
     *
     * @return CustomerSegmentGroup
     */
    public function getSegmentGroupById($id)
    {
        return CustomerSegmentGroup::getById($id);
    }

    /**
     * @param array $segmentIds
     * @param string $conditionMode
     *
     * @return \Pimcore\Model\Object\Listing\Concrete
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND)
    {
        $list = $this->customerProvider->getList();
        $list->setUnpublished(false);

        $conditions = [];
        foreach ($segmentIds as $segmentId) {
            $conditions[] = '(o_id in (select src_id from object_relations_1 where dest_id = '.$list->quote(
                    $segmentId
                ).'))';
        }

        if (sizeof($conditions)) {
            $list->setCondition('('.implode(' '.$conditionMode.' ', $conditions).')');
        }

        return $list;
    }

    /**
     * @param array $params
     *
     * @return CustomerSegment\Listing
     */
    public function getSegments(array $params = [])
    {
        /**
         * @var CustomerSegment\Listing $list;
         */
        $list = CustomerSegment::getList();
        $list->setUnpublished(false);

        return $list;
    }

    /**
     * @param array $params
     *
     * @return CustomerSegmentGroup\Listing
     */
    public function getSegmentGroups()
    {
        $list = CustomerSegmentGroup::getList();
        $list->setUnpublished(false);

        return $list;
    }

    /**
     * @param bool $changesQueueOnly
     * @param string|null $segmentBuilderServiceId
     * @param int[]|null $customQueue
     * @param bool|null $activeState
     * @param array $options
     *
     * @return void
     */
    public function buildCalculatedSegments(
        $changesQueueOnly = true,
        $segmentBuilderServiceId = null,
        array $customQueue = null,
        $activeState = null,
        $options = [],
        $captureSignals = false
    ) {
        $logger = $this->getLogger();
        $logger->notice('start segment building');

        $backup = $this->customerSaveManager->getSegmentBuildingHookEnabled();
        $this->customerSaveManager->setSegmentBuildingHookEnabled(false);

        if(!is_null($segmentBuilderServiceId)) {
            $segmentBuilders = [\Pimcore::getContainer()->get($segmentBuilderServiceId)];
        } else {
            $segmentBuilders = $this->segmentBuilders;
        }

        self::prepareSegmentBuilders($segmentBuilders);

        $customerList = $this->customerProvider->getList();
        // don't modify queue
        $removeCustomerFromQueue = is_null($segmentBuilderServiceId);

        $conditionParts = [];
        $conditionVariables = null;

        if (!empty($customQueue)) {
            // restrict to given customer
            $customerIds = array_filter($customQueue, 'is_numeric');
            if (!empty($customerIds)) {
                $conditionParts[] = sprintf('o_id in (%s)', implode(',', $customQueue));
            } else {
                // capture empty
                $conditionParts[] = '0 = 1';
            }
            // don't modify queue
            $removeCustomerFromQueue = false;
        } elseif ($changesQueueOnly) {
            $conditionParts[] = sprintf('o_id in (select customerId from %s)', self::CHANGES_QUEUE_TABLE);
        }

        if ($activeState !== null) {
            if ($activeState === true) {
                // active only
                $conditionParts[] = 'active = 1';
            } elseif ($activeState === false) {
                // inactive only
                $conditionParts[] = '(active IS NULL OR active != 1)';
            }
        }

        if (!empty($conditionParts)) {
            $customerList->setCondition(implode(' AND ', $conditionParts), $conditionVariables);
        }
        $customerList->setOrderKey('o_id');

        // parse options
        $desiredPageSize = isset($options['pageSize']) && (is_int($options['pageSize']) || ctype_digit(
                $options['pageSize']
            )) ? (int)$options['pageSize'] : null;
        $desiredStartPage = isset($options['startPage']) && (is_int($options['startPage']) || ctype_digit(
                $options['startPage']
            )) ? (int)$options['startPage'] : null;
        $desiredEndPage = isset($options['endPage']) && (is_int($options['endPage']) || ctype_digit(
                $options['endPage']
            )) ? (int)$options['endPage'] : null;
        $desiredPages = isset($options['pages']) && (is_int($options['pages']) || ctype_digit(
                $options['pages']
            )) ? (int)$options['pages'] : null;

        $logger->notice(
            sprintf(
                'Pre-fetching all ids via adapter for speedup and coherent paging '
            )
        );
        // note: listing is now constant and may be flushed per iteration
        $paginator = new Paginator(
            new \CustomerManagementFrameworkBundle\Pimcore\Model\Tool\ListingAdapter($customerList)
        );

        $pageSize = $desiredPageSize !== null && $desiredPageSize > 0 ? $desiredPageSize : 250;
        $paginator->setItemCountPerPage($pageSize);
        $totalAmount = $paginator->getTotalItemCount();
        $totalPages = $paginator->count();

        $startPage = $desiredStartPage !== null && $desiredStartPage > 0 ? min($totalPages, $desiredStartPage) : 1;
        $endPage = $totalPages;
        if ($desiredPages !== null && $desiredPages >= 0) {
            $endPage = min($totalPages, $startPage + $desiredPages);
        } elseif ($desiredEndPage > 0 && $desiredEndPage > $startPage) {
            $endPage = min($totalPages, $desiredEndPage);
        }

        $taskTotalAmount = min((($endPage - $startPage) + 1) * $pageSize, $totalAmount);

        $customerQueueRemoval = [];

        $flushQueue = function ($queue) {
            $queueSize = count($queue);
            if ($queueSize > 0) {
                $this->getLogger()->notice(
                    sprintf(
                        'Flushing queue of size %d',
                        $queueSize
                    )
                );

                foreach (array_chunk($queue, 50) as $nextChunk) {
                    try {
                        $removedAmount = Db::get()->deleteWhere(
                            self::CHANGES_QUEUE_TABLE,
                            sprintf('customerId IN (%s)', implode(',', $nextChunk))
                        );

                        $this->getLogger()->notice(
                            sprintf(
                                'Removed %d / %d customer from queue of size %d',
                                $removedAmount,
                                count($nextChunk),
                                $queueSize
                            )
                        );
                    } catch (\Exception $e) {
                        $this->getLogger()->error(sprintf('Failed to flush queue due too %s!', $e->getMessage()));
                    }
                }
            }

            return [];
        };

        $stopFurtherProcessing = false;
        if ($captureSignals) {
            $stopProcessingHook = function ($signal) use (&$stopFurtherProcessing) {
                $stopFurtherProcessing = true;
                $this->getLogger()->error(
                    sprintf(
                        'Captured signal "%d", stopping further processing',
                        $signal
                    )
                );
            };
            $logger->warning('Enabling signal listeing (Ctrl+C, Kill) during processing...');
            // kill
            @pcntl_signal(SIGTERM, $stopProcessingHook);
            // capture ctrl+c
            @pcntl_signal(SIGINT, $stopProcessingHook);
        }

        $progressCount = max((int)($pageSize / 10), 1);
        $progressTime = $startTime = time();
        $itemCount = 1;
        try {
            for ($pageNumber = $startPage; $pageNumber <= $endPage && $pageNumber <= $totalPages && !$stopFurtherProcessing; $pageNumber++) {
                $logger->notice(
                    sprintf(
                        'Building segments for %d / %d customers in total, currently at page %d / %d out of %d total pages',
                        $taskTotalAmount,
                        $totalAmount,
                        $pageNumber,
                        $endPage,
                        $totalPages
                    )
                );

                $paginator->setCurrentPageNumber($pageNumber);
                /** @var CustomerInterface $customer */
                foreach ($paginator as $customer) {
                    if ($itemCount % $progressCount === 0) {
                        $remaining = $totalAmount - $itemCount;
                        $taskRemaining = $taskTotalAmount - $itemCount;

                        $currentTime = time();
                        // avg seconds per customer
                        $seconds = ($currentTime - $progressTime) / $progressCount;

                        $estimatedCompletionSeconds = $remaining * $seconds;
                        $estimatedCompletionMinutes = $estimatedCompletionSeconds / 60;
                        $estimatedCompletionHours = $estimatedCompletionMinutes / 60;

                        $taskEstimatedCompletionSeconds = $taskRemaining * $seconds;
                        $taskEstimatedCompletionMinutes = $taskEstimatedCompletionSeconds / 60;
                        $taskEstimatedCompletionHours = $taskEstimatedCompletionMinutes / 60;

                        $logger->notice(
                            sprintf(
                                'Progress at %.2F - %d / %d (%d) - avg at %.2F s/item, completion-total in (%d s, %d m, %d h), completion-task in (%d s, %d m, %d h)',
                                round($itemCount / $taskTotalAmount, 4) * 100,
                                $itemCount,
                                $taskTotalAmount,
                                $totalAmount,
                                $seconds,
                                $estimatedCompletionSeconds,
                                $estimatedCompletionMinutes,
                                $estimatedCompletionHours,
                                $taskEstimatedCompletionSeconds,
                                $taskEstimatedCompletionMinutes,
                                $taskEstimatedCompletionHours
                            )
                        );

                        $progressTime = $currentTime;
                    }

                    foreach ($segmentBuilders as $segmentBuilder) {
                        try {
                            $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
                        } catch (\Exception $e) {
                            $this->getLogger()->error($e);
                        }
                    }

                    $this->saveMergedSegments($customer);

                    $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\ExecuteSegmentBuilders(
                        $customer
                    );
                    \Pimcore::getEventDispatcher()->dispatch($event->getName(), $event);

                    if ($removeCustomerFromQueue) {
                        // delay queue removal to prevent paging issue
                        $customerQueueRemoval[] = $customer->getId();
                    }
                    $itemCount += 1;

                    if ($captureSignals) {
                        // capture events
                        @pcntl_signal_dispatch();
                        if ($stopFurtherProcessing) {
                            // stop processing captured signal
                            break;
                        }
                    }
                }

                if (!$stopFurtherProcessing && $captureSignals) {
                    // capture events
                    @pcntl_signal_dispatch();
                }

                if (!$stopFurtherProcessing) {
                    $customerQueueRemoval = $flushQueue($customerQueueRemoval);
                    \Pimcore::collectGarbage();
                }
            }
        } finally {
            $flushQueue($customerQueueRemoval);
        }

        $this->customerSaveManager->setSegmentBuildingHookEnabled($backup);
    }

    /**
     * @param CustomerInterface $customer
     */
    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer)
    {
        $segmentBuilders = $this->segmentBuilders;
        self::prepareSegmentBuilders($segmentBuilders, true);

        foreach ($segmentBuilders as $segmentBuilder) {
            if (!$segmentBuilder->executeOnCustomerSave()) {
                continue;
            }

            $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
        }

        $this->saveMergedSegments($customer);
    }

    public function executeSegmentBuilderMaintenance()
    {
        foreach ($this->segmentBuilders as $segmentBuilder) {
            $segmentBuilder->maintenance($this);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param SegmentBuilderInterface $segmentBuilder
     */
    protected function applySegmentBuilderToCustomer(
        CustomerInterface $customer,
        SegmentBuilderInterface $segmentBuilder
    ) {
        $this->getLogger()->info(
            sprintf('apply segment builder %s to customer %s', $segmentBuilder->getName(), (string)$customer)
        );
        $segmentBuilder->calculateSegments($customer, $this);
    }

    /**
     * @param                      $segmentReference
     * @param CustomerSegmentGroup $segmentGroup
     * @param null $calculated
     *
     * @return mixed
     */
    public function getSegmentByReference($segmentReference, CustomerSegmentGroup $segmentGroup = null, $calculated = null)
    {
        $list = new \Pimcore\Model\Object\CustomerSegment\Listing;

        $calculatedCondition = '';
        if (!is_null($calculated)) {
            $calculatedCondition = 'and calculated = 1';
            if (!$calculated) {
                $calculatedCondition = 'and (calculated is null or calculated = 0)';
            }
        }

        if($segmentGroup) {
            $list->setCondition(
                'reference = ? and group__id = ? '.$calculatedCondition,
                [$segmentReference, $segmentGroup->getId()]
            );
        } else {
            $list->setCondition(
                'reference = ? '.$calculatedCondition,
                [$segmentReference]
            );
        }

        $list->setUnpublished(true);
        $list->setLimit(1);
        $list = $list->load();

        if (!empty($list)) {
            return $list[0];
        }
    }

    /**
     * @param string $segmentName
     * @param CustomerSegmentGroup|string $segmentGroup
     * @param null $segmentReference
     * @param bool $calculated
     * @param null $subFolder
     *
     * @return mixed|CustomerSegment
     *
     * @throws \Exception
     */
    public function createSegment(
        $segmentName,
        $segmentGroup,
        $segmentReference = null,
        $calculated = true,
        $subFolder = null
    ) {
        if ($segmentGroup instanceof CustomerSegmentGroup && $segmentGroup->getCalculated() != $calculated) {
            throw new \Exception(
                sprintf(
                    "it's not possible to create a %s segment within a %s segment group",
                    $calculated ? 'calculated' : 'manual',
                    $calculated ? 'manual' : 'calculated'
                )
            );
        }

        $segmentGroup = self::createSegmentGroup($segmentGroup, $segmentGroup, $calculated);

        if ($segment = $this->getSegmentByReference($segmentReference, $segmentGroup)) {
            return $segment;
        }

        $parent = $segmentGroup;
        if (!is_null($subFolder)) {
            $subFolder = explode('/', $subFolder);
            $folder = [];
            foreach ($subFolder as $f) {
                if ($f = Objects::getValidKey($f)) {
                    $folder[] = $f;
                }
            }
            $subFolder = implode('/', $folder);

            if ($subFolder) {
                $fullPath = str_replace('//', '/', $segmentGroup->getFullPath().'/'.$subFolder);
                $parent = Service::createFolderByPath($fullPath);
            }
        }

        $segment = new CustomerSegment();
        $segment->setParent($parent);
        $segment->setKey(Objects::getValidKey($segmentReference ?: $segmentName));
        $segment->setName($segmentName);
        $segment->setReference($segmentReference);
        $segment->setPublished(true);
        $segment->setCalculated($calculated);
        $segment->setGroup($segmentGroup);
        Objects::checkObjectKey($segment);
        $segment->save();

        return $segment;
    }

    /**
     * @param string $segmentReference
     * @param CustomerSegmentGroup|string $segmentGroup
     * @param null $segmentName
     * @param null $subFolder
     *
     * @return mixed|CustomerSegment
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null, $subFolder = null)
    {
        return $this->createSegment(
            $segmentName ?: $segmentReference,
            $segmentGroup,
            $segmentReference,
            true,
            $subFolder
        );
    }

    /**
     * @param       $segmentGroupName
     * @param null $segmentGroupReference
     * @param bool $calculated
     * @param array $values
     *
     * @return CustomerSegmentGroup
     */
    public function createSegmentGroup(
        $segmentGroupName,
        $segmentGroupReference = null,
        $calculated = true,
        array $values = []
    ) {
        if ($segmentGroupName instanceof CustomerSegmentGroup) {
            return $segmentGroupName;
        }

        if ($segmentGroup = $this->getSegmentGroupByReference($segmentGroupReference, $calculated)) {
            return $segmentGroup;
        }

        $segmentFolder = Service::createFolderByPath(
            $calculated ? $this->segmentFolderCalculated : $this->segmentFolderManual
        );

        $segmentGroup = new CustomerSegmentGroup();
        $segmentGroup->setParent($segmentFolder);
        $segmentGroup->setPublished(true);
        $segmentGroup->setKey(Objects::getValidKey($segmentGroupReference ?: $segmentGroupName));
        $segmentGroup->setCalculated($calculated);
        $segmentGroup->setName($segmentGroupName);
        $segmentGroup->setReference($segmentGroupReference);

        $segmentGroup->setValues($values);

        Objects::checkObjectKey($segmentGroup);
        $segmentGroup->save();

        return $segmentGroup;
    }

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array $values
     */
    public function updateSegmentGroup(CustomerSegmentGroup $segmentGroup, array $values = [])
    {
        $calculatedState = $segmentGroup->getCalculated();
        $segmentGroup->setValues($values);
        $segmentGroup->setKey(Objects::getValidKey($segmentGroup->getReference() ?: $segmentGroup->getName()));
        Objects::checkObjectKey($segmentGroup);

        if (isset($values['calculated'])) {
            if ((bool)$values['calculated'] != $calculatedState) {
                foreach ($this->getSegmentsFromSegmentGroup($segmentGroup) as $segment) {
                    if ($segment->getCalculated() != (bool)$values['calculated']) {
                        $segment->setCalculated((bool)$values['calculated']);
                        $segment->save();
                    }
                }

                $segmentGroup->setParent(
                    Service::createFolderByPath(
                        (bool)$values['calculated'] ? $this->segmentFolderCalculated : $this->segmentFolderManual
                    )
                );
            }
        }

        $segmentGroup->save();
    }

    /**
     * @param CustomerSegment $segment
     * @param array $values
     *
     * @throws \Exception
     */
    public function updateSegment(CustomerSegment $segment, array $values = [])
    {
        $segment->setValues($values);

        if (!empty($values['group'])) {
            if (!$segmentGroup = CustomerSegmentGroup::getById($values['group'])) {
                throw new \Exception('SegmentGroup with id %s not found', $values['group']);
            }

            $segment->setGroup($segmentGroup);
            $segment->setParent($segmentGroup);
        }

        if (isset($values['calculated']) && $group = $segment->getGroup()) {
            if ($group->getCalculated() != (bool)$values['calculated']) {
                throw new \Exception("calculated state of segment cannot be different then for it's segment group");
            }
        }

        $segment->setKey(Objects::getValidKey($segment->getReference() ?: $segment->getName()));
        Objects::checkObjectKey($segment);

        $segment->save();
    }

    /**
     * @param $segmentGroupReference
     * @param $calculated
     *
     * @return mixed
     */
    public function getSegmentGroupByReference($segmentGroupReference, $calculated)
    {
        if (!is_null($segmentGroupReference)) {
            $list = new \Pimcore\Model\Object\CustomerSegmentGroup\Listing;
            $list->setUnpublished(true);
            $list->setCondition(
                'reference = ? and '.($calculated ? '(calculated = 1)' : '(calculated is null or calculated = 0)'),
                $segmentGroupReference
            );
            $list->setUnpublished(true);
            $list->setLimit(1);
            $list = $list->load();

            return $list[0];
        }
    }

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array $ignoreSegments
     *
     * @return array
     */
    public function getSegmentsFromSegmentGroup(CustomerSegmentGroup $segmentGroup, array $ignoreSegments = [])
    {
        $ignoreIds = [];
        foreach ($ignoreSegments as $ignoreSegment) {
            $ignoreIds[] = $ignoreSegment->getId();
        }

        $ignoreCondition = '';
        if (sizeof($ignoreIds)) {
            $ignoreCondition = ' and o_id not in('.implode(',', $ignoreIds).')';
        }

        $list = new CustomerSegment\Listing;
        $list->setUnpublished(true);
        $list->setCondition('group__id = ?'.$ignoreCondition, $segmentGroup->getId());
        $list->setOrderKey('name');
        $result = $list->load();

        return $result ?: [];
    }

    /**
     * @param CustomerInterface $customer
     */
    public function addCustomerToChangesQueue(CustomerInterface $customer)
    {
        Db::get()->query(
            sprintf('insert ignore into %s set customerId = %d', self::CHANGES_QUEUE_TABLE, $customer->getId())
        );
    }

    /**
     * @param CustomerSegmentInterface $segment
     */
    public function preSegmentUpdate(CustomerSegmentInterface $segment)
    {
        if ($segment instanceof Concrete) {
            $parent = $segment;

            $group = null;
            while ($parent) {
                $parent = $parent->getParent();

                if ($parent instanceof CustomerSegmentGroup) {
                    $group = $parent;
                    break;
                }
            }

            if ($group) {
                $segment->setGroup($parent);
            } else {
                $segment->setGroup(null);
            }
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param CustomerSegmentInterface $segment
     *
     * @return bool
     */
    public function customerHasSegment(CustomerInterface $customer, CustomerSegmentInterface $segment)
    {
        if ($segments = $customer->getAllSegments()) {
            foreach ($segments as $s) {
                if ($s->getId() == $segment->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return segments of given customers which are within given customer segment group.
     *
     * @param CustomerInterface $customer
     * @param CustomerSegmentGroup|string $group
     *
     * @return CustomerSegmentInterface[]
     */
    public function getCustomersSegmentsFromGroup(CustomerInterface $customer, $group)
    {
        $group = $group instanceof CustomerSegmentGroup ? $group : $this->getSegmentGroupByReference($group, true);

        if (!$group instanceof CustomerSegmentGroup) {
            return [];
        }

        if (!$segments = $customer->getAllSegments()) {
            return [];
        }

        $result = [];
        foreach ($segments as $segment) {
            if ($segment->getGroup() && $segment->getGroup()->getId() == $group->getId()) {
                $result[] = $segment;
            }
        }

        return $result;
    }

    /**
     * @param SegmentBuilderInterface[] $segmentBuilders
     * @param bool $ignoreAsyncSegmentBuilders
     */
    protected function prepareSegmentBuilders(array $segmentBuilders, $ignoreAsyncSegmentBuilders = false)
    {
        foreach ($segmentBuilders as $segmentBuilder) {
            if ($ignoreAsyncSegmentBuilders && !$segmentBuilder->executeOnCustomerSave()) {
                continue;
            }

            $this->getLogger()->notice(sprintf('prepare segment builder %s', $segmentBuilder->getName()));
            $segmentBuilder->prepare($this);
        }
    }

    /**
     * @inheritdoc
     */
    public function mergeSegments(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $hintForNotes = null
    ) {
        \Pimcore::getContainer()->get('cmf.segment_manager.segment_merger')->mergeSegments(
            $customer,
            $addSegments,
            $deleteSegments,
            $hintForNotes
        );
    }

    /**
     * @inheritdoc
     */
    public function saveMergedSegments(CustomerInterface $customer)
    {
        \Pimcore::getContainer()->get('cmf.segment_manager.segment_merger')->saveMergedSegments($customer);
    }


    public function addSegmentBuilder(SegmentBuilderInterface $segmentBuilder)
    {
        $this->segmentBuilders[] = $segmentBuilder;
    }

}
