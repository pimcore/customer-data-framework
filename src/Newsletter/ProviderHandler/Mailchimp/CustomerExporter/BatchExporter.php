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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\CustomerExporter;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use DrewM\MailChimp\Batch;
use Pimcore\Model\Element\ElementInterface;

class BatchExporter extends AbstractExporter
{
    /**
     * @var int
     */
    protected $maxCheckIterations = 10;

    /**
     * Wait for n ms before trying to get batch status
     *
     * @var int
     */
    protected $initialCheckSleepInterval = 7500;

    /**
     * Wait for n ms for each record before trying to get batch status
     *
     * @var int
     */
    protected $recordCheckSleepInterval = 30;

    /**
     * Base batch sleep interval (will be increased exponentially on error)
     *
     * @var int
     */
    protected $sleepStepInterval = 500;

    /**
     * Run the actual export
     *
     * @param NewsletterQueueItemInterface[] = $items
     * @param Mailchimp $mailchimpProviderHandler
     *
     */
    public function export(array $items, Mailchimp $mailchimpProviderHandler)
    {
        $apiClient = $this->apiClient;
        $batch = $apiClient->new_batch();

        foreach ($items as $item) {
            $customer = $item->getCustomer();

            // schedule batch operation
            if ($item->getOperation() == NewsletterQueueInterface::OPERATION_UPDATE) {
                if ($item->getCustomer()->needsExportByNewsletterProviderHandler($mailchimpProviderHandler)) {
                    // entry to send to API
                    $entry = $mailchimpProviderHandler->buildEntry($customer);
                    $this->createBatchUpdateOperation($batch, $customer, $entry, $mailchimpProviderHandler);
                } else {
                    $item->setOverruledOperation(NewsletterQueueInterface::OPERATION_DELETE);
                    $this->createBatchDeleteOperation($batch, $item, $mailchimpProviderHandler);
                }
            } elseif ($item->getOperation() == NewsletterQueueInterface::OPERATION_DELETE) {
                $this->createBatchDeleteOperation($batch, $item, $mailchimpProviderHandler);
            }
        }

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][BATCH] Executing batch'
            )
        );

        $result = $batch->execute();

        if ($apiClient->success()) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][BATCH] Executed batch. ID is %s',
                    $result['id']
                )
            );
        } else {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][BATCH] Batch request failed: %s %s',
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                )
            );
        }

        $recordSleepInterval = count($items) * $this->recordCheckSleepInterval;
        $totalSleepInterval = $this->initialCheckSleepInterval + $recordSleepInterval;

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][BATCH][CHECK] Sleeping for %dms (initial) + %dms (%dms per record) = %d ms before checking batch results',
                $this->initialCheckSleepInterval,
                $recordSleepInterval,
                $this->recordCheckSleepInterval,
                $totalSleepInterval
            )
        );

        // usleep takes microseconds as input
        usleep($totalSleepInterval * 1000);

        // get batch status (re-try until batch is done and back-off exponentially)
        $batchStatus = $this->checkBatchStatus($batch);

        if (!$batchStatus) {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][BATCH] Failed to check batch status after a maximum of %d iterations',
                    $this->maxCheckIterations
                )
            );

            return;
        }

        // update records which were exported successfully with export notes
        $this->handleBatchStatus($batchStatus, $items, $mailchimpProviderHandler);
    }

    /**
     * @param Batch $batch
     * @param CustomerInterface|ElementInterface $customer
     * @param array $entry
     * @param Mailchimp $mailchimpProviderHandler
     */
    protected function createBatchUpdateOperation(Batch $batch, CustomerInterface $customer, array $entry, Mailchimp $mailchimpProviderHandler)
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        $objectId = $customer->getId();
        $remoteId = $apiClient->subscriberHash($entry['email_address']);

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][CUSTOMER %s][%s][BATCH] Adding customer with remote ID %s',
                $objectId,
                $mailchimpProviderHandler->getShortcut(),
                $remoteId
            )
        );

        if ($exportService->wasExported($customer, $mailchimpProviderHandler->getListId())) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s][BATCH] Customer already exists remotely with remote ID %s',
                    $objectId,
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId
                )
            );
        } else {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s][BATCH] Customer was not exported yet',
                    $objectId,
                    $mailchimpProviderHandler->getShortcut()
                )
            );
        }

        $batch->put(
            (string)$customer->getId(),
            $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId)),
            $entry
        );
    }

    /**
     * @param Batch $batch
     * @param CustomerInterface|ElementInterface $customer
     * @param array $entry
     * @param Mailchimp $mailchimpProviderHandler
     */
    protected function createBatchDeleteOperation(Batch $batch, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        if($mailchimpProviderHandler->doesOtherSubscribedCustomerWithEmailExist($item->getEmail(), $item->getCustomerId())) {

            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Deletion skipped as another subscribed customer with the same email exists.',
                    $item->getCustomerId(),
                    $mailchimpProviderHandler->getShortcut()
                )
            );

            $item->setSuccessfullyProcessed(true);
            return;
        }

        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        $objectId = $item->getCustomerId();
        $remoteId = $apiClient->subscriberHash($item->getEmail());

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][CUSTOMER %s][%s][BATCH] Adding deletion of customer with remote ID %s',
                $objectId,
                $mailchimpProviderHandler->getShortcut(),
                $remoteId
            )
        );

        $batch->delete(
            (string) $item->getCustomerId(),
            $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
        );
    }

    /**
     * Check batch status and back off exponentially after errors
     *
     * @param Batch $batch
     * @param int $iteration
     *
     * @return array|bool
     */
    protected function checkBatchStatus(Batch $batch, $iteration = 0)
    {
        if ($iteration > $this->maxCheckIterations) {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][BATCH][CHECK %d] Reached max check iterations, aborting',
                    $iteration
                )
            );

            return false;
        }

        if ($iteration > 0) {
            $sleep = $this->sleepStepInterval * pow(2, $iteration - 1);

            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][BATCH][CHECK %d] Sleeping for %d ms before checking batch status',
                    $iteration,
                    $sleep
                )
            );

            // usleep takes microseconds as input
            usleep($sleep * 1000);
        }

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][BATCH][CHECK %d] Checking status',
                $iteration
            )
        );

        $apiClient = $this->apiClient;
        $result = $batch->check_status();

        if ($apiClient->success()) {
            if ($result['status'] === 'finished') {
                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][BATCH][CHECK %d] Batch is finished',
                        $iteration
                    )
                );

                return $result;
            } else {
                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][BATCH][CHECK %d] Batch is not finished yet. Status is "%s"',
                        $iteration,
                        $result['status']
                    )
                );
            }
        } else {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][BATCH][CHECK %d] Batch status request failed: %s %s',
                    $iteration,
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                )
            );
        }

        return $this->checkBatchStatus($batch, $iteration + 1);
    }

    /**
     * Update exported records from batch request with export notes and handle errored records
     *
     * @param array $result
     * @param NewsletterQueueItemInterface[] $items
     * @param Mailchimp $mailchimpProviderHandler
     */
    protected function handleBatchStatus(array $result, array $items, $mailchimpProviderHandler)
    {
        if ($result['errored_operations'] === 0) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][BATCH] Batch has no errored operations, updating export note for all records (no need to fetch detailed results)'
                )
            );

            foreach ($items as $item) {
                $this->processSuccessfullItem($mailchimpProviderHandler, $item);
            }
        } else {
            try {
                $data = gzdecode(file_get_contents($result['response_body_url']));
                $temp = tempnam('', '') . '.tar';
                file_put_contents($temp, $data);
                $tar_object = new \Archive_Tar($temp);
                $v_list = $tar_object->listContent();
                $contents = null;

                foreach ($v_list as $item) {
                    if (strpos($item['filename'], '.json') !== false) {
                        $contents = $tar_object->extractInString($item['filename']);
                    }
                }
                unlink($temp);
                $contents = json_decode($contents, true);

                $failedOperations = [];
                foreach ($contents as $operation) {
                    if ($operation['status_code'] != 200) {
                        $failedOperations[$operation['operation_id']] = $operation;
                    }
                }

                foreach ($items as $item) {
                    if (!isset($failedOperations[$item->getCustomerId()])) {
                        $this->processSuccessfullItem($mailchimpProviderHandler, $item);
                    } else {
                        $this->processFailedItem($mailchimpProviderHandler, $item, $failedOperations[$item->getCustomerId()]['response']);
                    }
                }
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
            }
        }
    }

    protected function processSuccessfullItem(Mailchimp $mailchimpProviderHandler, NewsletterQueueItemInterface $item)
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        /** @var MailchimpAwareCustomerInterface|ElementInterface $customer */
        $customer = $item->getCustomer();


        $operation = $item->getOverruledOperation() ?: $item->getOperation();

        // add note
        if ($operation == NewsletterQueueInterface::OPERATION_UPDATE) {

            $entry = $mailchimpProviderHandler->buildEntry($customer);
            $remoteId = $apiClient->subscriberHash($entry['email_address']);

            $exportService
                ->createExportNote($customer, $mailchimpProviderHandler->getListId(), $remoteId, null, 'Mailchimp Export [' . $mailchimpProviderHandler->getShortcut() . ']', ['exportdataMd5' => $exportService->getMd5($entry)])
                ->save();

            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Export was successful. Remote ID is %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId
                ),
                [
                    'relatedObject' => $customer
                ]
            );
        } elseif ($customer) {
            $entry = $mailchimpProviderHandler->buildEntry($customer);
            $remoteId = $apiClient->subscriberHash($entry['email_address']);

            $exportService
                ->createExportNote($customer, $mailchimpProviderHandler->getListId(), $remoteId, null, 'Mailchimp Deletion [' . $mailchimpProviderHandler->getShortcut() . ']')
                ->save();

            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Deletion was successful. Remote ID is %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId
                ),
                [
                    'relatedObject' => $customer
                ]
            );
        }

        if($customer) {

            $entry = $mailchimpProviderHandler->buildEntry($customer);

            $status = isset($entry['status']) ? $entry['status'] : $entry['status_if_new'];
            $mailchimpProviderHandler->updateMailchimpStatus($customer, $status);

        }
        $item->setSuccessfullyProcessed(true);
    }

    protected function processFailedItem(Mailchimp $mailchimpProviderHandler, NewsletterQueueItemInterface $item, $message)
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        /** @var MailchimpAwareCustomerInterface|ElementInterface $customer */
        $customer = $item->getCustomer();

        if (!$customer) {
            return;
        }

        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $apiClient->subscriberHash($entry['email_address']);

        $operation = $item->getOverruledOperation() ?: $item->getOperation();

        // add note
        if ($operation == NewsletterQueueInterface::OPERATION_UPDATE) {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Export failed. Remote ID is %s. [error: %s]',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId,
                    $message
                ),
                [
                    'relatedObject' => $customer
                ]
            );
        } elseif ($customer) {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Deletion failed. Remote ID is %s. [error: %s]',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId,
                    $message
                ),
                [
                    'relatedObject' => $customer
                ]
            );
        }
    }
}
