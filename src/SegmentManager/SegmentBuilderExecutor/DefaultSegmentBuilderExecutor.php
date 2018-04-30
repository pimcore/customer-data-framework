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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentBuilder\SegmentBuilderInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Zend\Paginator\Paginator;

class DefaultSegmentBuilderExecutor implements SegmentBuilderExecutorInterface
{
    use LoggerAware;

    const CHANGES_QUEUE_TABLE = 'plugin_cmf_segmentbuilder_changes_queue';

    /**
     * @var SegmentManagerInterface
     */
    protected $segmentManager;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var CustomerSaveManagerInterface
     */
    protected $customerSaveManager;

    public function __construct(SegmentManagerInterface $segmentManager, CustomerProviderInterface $customerProvider, CustomerSaveManagerInterface $customerSaveManager)
    {
        $this->segmentManager = $segmentManager;
        $this->customerProvider = $customerProvider;
        $this->customerSaveManager = $customerSaveManager;
    }

    /**
     * @param CustomerInterface $customer
     */
    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer)
    {
        self::prepareSegmentBuilders($this->segmentManager->getSegmentBuilders(), true);

        foreach ($this->segmentManager->getSegmentBuilders() as $segmentBuilder) {
            if (!$segmentBuilder->executeOnCustomerSave()) {
                continue;
            }

            $this->applySegmentBuilderToCustomer($customer, $segmentBuilder);
        }

        $this->segmentManager->saveMergedSegments($customer);
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

        if (!is_null($segmentBuilderServiceId)) {
            $segmentBuilders = [\Pimcore::getContainer()->get($segmentBuilderServiceId)];
        } else {
            $segmentBuilders = $this->segmentManager->getSegmentBuilders();
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

        if (!empty($conditionParts)) {
            $customerList->setCondition(implode(' AND ', $conditionParts), $conditionVariables);
        }


        if ($activeState !== null) {
            if ($activeState === true) {
                // active only
                $this->customerProvider->addActiveCondition($customerList);
            } elseif ($activeState === false) {
                // inactive only
                $this->customerProvider->addInActiveCondition($customerList);
            }
        }

        $customerList->setOrderKey('o_id');

        // parse options
        $desiredPageSize = $this->getIntOption($options, 'pageSize');
        $desiredStartPage = $this->getIntOption($options, 'startPage');
        $desiredEndPage = $this->getIntOption($options, 'endPage');
        $desiredPages = $this->getIntOption($options, 'pages');

        $logger->notice('Pre-fetching all ids via adapter for speedup and coherent paging');

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

                    $this->segmentManager->saveMergedSegments($customer);

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

    protected function getIntOption(array $options, $option)
    {
        return isset($options[$option]) && (is_int($options[$option]) || ctype_digit($options[$option]))
            ? (int)$options[$option] : null;
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

    public function executeSegmentBuilderMaintenance()
    {
        foreach ($this->segmentManager->getSegmentBuilders() as $segmentBuilder) {
            $segmentBuilder->maintenance($this->segmentManager);
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
        $segmentBuilder->calculateSegments($customer, $this->segmentManager);
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
            $segmentBuilder->prepare($this->segmentManager);
        }
    }
}
