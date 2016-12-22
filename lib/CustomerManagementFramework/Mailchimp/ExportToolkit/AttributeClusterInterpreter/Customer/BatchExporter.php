<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter\Customer;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use DrewM\MailChimp\Batch;
use Pimcore\Model\Element\ElementInterface;

class BatchExporter extends AbstractExporter
{
    /**
     * @var int
     */
    protected $maxCheckIterations = 3;

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
    protected $recordCheckSleepInterval = 150;

    /**
     * Base batch sleep interval (will be increased exponentially on error)
     *
     * @var int
     */
    protected $sleepStepInterval = 500;

    /**
     * Run the actual export
     */
    public function export()
    {
        $apiClient = $this->apiClient;
        $batch     = $apiClient->new_batch();

        $objectIds = $this->interpreter->getObjectIds();

        foreach ($objectIds as $objectId) {
            $customer = $this->getCustomer($objectId);

            // entry to send to API
            $entry = $this->interpreter->buildEntry($customer);

            // schedule batch operation
            $this->createBatchOperation($batch, $customer, $entry);
        }

        $this->logger->info(sprintf(
            '[MailChimp][BATCH] Executing batch'
        ));

        $result = $batch->execute();

        if ($apiClient->success()) {
            $this->logger->info(sprintf(
                '[MailChimp][BATCH] Executed batch. ID is %s',
                $result['id']
            ));
        } else {
            $this->logger->error(sprintf(
                '[MailChimp][BATCH] Batch request failed: %s %s',
                json_encode($apiClient->getLastError()),
                $apiClient->getLastResponse()['body']
            ));
        }

        $recordSleepInterval = count($objectIds) * $this->recordCheckSleepInterval;
        $totalSleepInterval  = $this->initialCheckSleepInterval + $recordSleepInterval;

        $this->logger->info(sprintf(
            '[MailChimp][BATCH][CHECK] Sleeping for %dms (initial) + %dms (%dms per record) = %d ms before checking batch results',
            $this->initialCheckSleepInterval,
            $recordSleepInterval,
            $this->recordCheckSleepInterval,
            $totalSleepInterval
        ));

        // usleep takes microseconds as input
        usleep($totalSleepInterval * 1000);

        // get batch status (re-try until batch is done and back-off exponentially)
        $batchStatus = $this->checkBatchStatus($batch);

        if (!$batchStatus) {
            $this->logger->error(sprintf(
                '[MailChimp][BATCH] Failed to check batch status after a maximum of %d iterations',
                $this->maxCheckIterations
            ));

            return;
        }

        // update records which were exported successfully with export notes
        $this->handleBatchStatus($batchStatus);
    }

    /**
     * @param Batch $batch
     * @param CustomerInterface|ElementInterface $customer
     * @param array $entry
     */
    protected function createBatchOperation(Batch $batch, CustomerInterface $customer, array $entry)
    {
        $exportService = $this->exportService;
        $apiClient     = $this->apiClient;

        $objectId = $customer->getId();
        $remoteId = $apiClient->subscriberHash($entry['email_address']);

        $this->logger->info(sprintf(
            '[MailChimp][CUSTOMER %s][BATCH] Adding customer with remote ID %s',
            $objectId,
            $remoteId
        ));

        if ($exportService->wasExported($customer)) {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s][BATCH] Customer already exists remotely with remote ID %s',
                $objectId,
                $remoteId
            ));
        } else {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s][BATCH] Customer was not exported yet',
                $objectId
            ));
        }

        $batch->put(
            (string)$customer->getId(),
            $exportService->getListResourceUrl(sprintf('members/%s', $remoteId)),
            $entry
        );
    }

    /**
     * Check batch status and back off exponentially after errors
     *
     * @param Batch $batch
     * @param int $iteration
     * @return array|bool
     */
    protected function checkBatchStatus(Batch $batch, $iteration = 0)
    {
        if ($iteration > $this->maxCheckIterations) {
            $this->logger->error(sprintf(
                '[MailChimp][BATCH][CHECK %d] Reached max check iterations, aborting',
                $iteration
            ));

            return false;
        }

        if ($iteration > 0) {
            $sleep = $this->sleepStepInterval * pow(2, $iteration - 1);

            $this->logger->info(sprintf(
                '[MailChimp][BATCH][CHECK %d] Sleeping for %d ms before checking batch status',
                $iteration,
                $sleep
            ));

            // usleep takes microseconds as input
            usleep($sleep * 1000);
        }

        $this->logger->info(sprintf(
            '[MailChimp][BATCH][CHECK %d] Checking status',
            $iteration
        ));

        $apiClient = $this->apiClient;
        $result    = $batch->check_status();

        if ($apiClient->success()) {
            if ($result['status'] === 'finished') {
                $this->logger->info(sprintf(
                    '[MailChimp][BATCH][CHECK %d] Batch is finished',
                    $iteration
                ));

                return $result;
            } else {
                $this->logger->warning(sprintf(
                    '[MailChimp][BATCH][CHECK %d] Batch is not finished yet. Status is "%s"',
                    $iteration,
                    $result['status']
                ));
            }
        } else {
            $this->logger->error(sprintf(
                '[MailChimp][BATCH][CHECK %d] Batch status request failed: %s %s',
                $iteration,
                json_encode($apiClient->getLastError()),
                $apiClient->getLastResponse()['body']
            ));
        }

        return $this->checkBatchStatus($batch, $iteration + 1);
    }

    /**
     * Update exported records from batch request with export notes and handle errored records
     *
     * @param array $result
     */
    protected function handleBatchStatus(array $result)
    {
        $exportService = $this->exportService;
        $apiClient     = $this->apiClient;

        if ($result['errored_operations'] === 0) {
            $this->logger->info(sprintf(
                '[MailChimp][BATCH] Batch has no errored operations, updating export note for all records (no need to fetch detailed results)'
            ));

            $objectIds = $this->interpreter->getObjectIds();
            foreach ($objectIds as $objectId) {
                /** @var CustomerInterface|ElementInterface $customer */
                $customer = Factory::getInstance()->getCustomerProvider()->getById($objectId);
                $remoteId = $apiClient->subscriberHash($this->interpreter->getDataEntry($objectId)['email_address']);

                // add note
                $exportService
                    ->createExportNote($customer, $remoteId)
                    ->save();
            }
        } else {
            // TODO in case the batch response contains errored operations - fetch the detailed result from response_body_url
            // which is a tar.gz containing JSON file(s), parse those results and show which records failed
        }
    }
}
