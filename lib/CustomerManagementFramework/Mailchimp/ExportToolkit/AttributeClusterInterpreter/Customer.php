<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use DrewM\MailChimp\Batch;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\AbstractObject;
use Pimcore\Model\Object\CustomerSegment;

class Customer extends AbstractMailchimpInterpreter
{
    /**
     * @var int
     */
    protected $batchThreshold = 10;

    /**
     * @var int
     */
    protected $batchMaxCheckIterations = 3;

    /**
     * Wait for n ms before trying to get batch status
     *
     * @var int
     */
    protected $batchInitialSleepInterval = 5000;

    /**
     * Wait for n ms for each record before trying to get batch status
     *
     * @var int
     */
    protected $batchSleepIntervalPerRecord = 150;

    /**
     * Base batch sleep interval (will be increased exponentially on error)
     *
     * @var int
     */
    protected $batchSleepStepSize = 500;

    /**
     * @var CustomerSegmentInterface[]
     */
    protected $segments;

    /**
     * @return CustomerSegmentInterface[]|CustomerSegment[]
     */
    protected function getSegments()
    {
        if (!$this->segments) {
            $this->segments = Factory::getInstance()->getSegmentManager()->getSegments([]);
        }

        return $this->segments;
    }

    /**
     * This method is executed after all objects are exported.
     * If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
     * For example it can be used to write all data to a xml file or commit a database transaction, etc.
     */
    public function commitData()
    {
        $dataCount = count($this->data);

        if ($dataCount <= $this->batchThreshold) {
            $this->logger->info(sprintf(
                '[MailChimp] Data count (%d) is below batch threshold (%d), sending one request per entry...',
                $dataCount,
                $this->batchThreshold
            ));

            $objectIds = array_keys($this->data);

            for ($i = 0; $i < $dataCount; $i++) {
                $this->commitSingle($objectIds[$i]);
            }
        } else {
            $this->logger->info(sprintf(
                '[MailChimp] Sending data as batch request'
            ));

            $this->commitBatch();
        }
    }

    /**
     * Export all customers in dataset to mailchimp
     */
    protected function commitBatch()
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();
        $batch         = $apiClient->new_batch();

        $objectIds = array_keys($this->data);

        foreach ($objectIds as $objectId) {
            /** @var CustomerInterface|ElementInterface $customer */
            $customer = Factory::getInstance()->getCustomerProvider()->getById($objectId);

            // entry to send to API
            $entry = $this->buildEntry($customer);

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

        $recordSleepInterval = count($objectIds) * $this->batchSleepIntervalPerRecord;
        $totalSleepInterval  = $recordSleepInterval + $this->batchInitialSleepInterval;

        $this->logger->info(sprintf(
            '[MailChimp][BATCH][CHECK] Sleeping for %dms (initial) + %dms (%dms per record) = %d ms before checking batch results',
            $this->batchInitialSleepInterval,
            $recordSleepInterval,
            $this->batchSleepIntervalPerRecord,
            $totalSleepInterval
        ));

        // usleep takes microseconds as input
        usleep($totalSleepInterval * 1000);

        // get batch status (re-try until batch is done and back-off exponentially)
        $batchStatus = $this->checkBatchStatus($batch);

        if (!$batchStatus) {
            $this->logger->error(sprintf(
                '[MailChimp][BATCH] Failed to check batch status after a maximum of %d iterations',
                $this->batchMaxCheckIterations
            ));
        }

        // update records which were exported successfully with export notes
        $this->handleBatchStatus($batchStatus);
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
        if ($iteration > $this->batchMaxCheckIterations) {
            $this->logger->error(sprintf(
                '[MailChimp][BATCH][CHECK %d] Reached max check iterations, aborting',
                $iteration
            ));

            return false;
        }

        if ($iteration > 0) {
            $sleep = $this->batchSleepStepSize * pow(2, $iteration - 1);

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

        $apiClient = $this->getExportService()->getApiClient();
        $result    = $batch->check_status();

        if ($apiClient->success()) {
            if ($result['status'] === 'finished') {
                $this->logger->info(sprintf(
                    '[MailChimp][BATCH][CHECK %d] Batch is finished',
                    $iteration,
                    $result['status']
                ));

                return $result;
            } else {
                $this->logger->info(sprintf(
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

        $this->checkBatchStatus($batch, $iteration + 1);
    }

    /**
     * Update exported records from batch request with export notes and handle errored records
     *
     * @param array $result
     */
    protected function handleBatchStatus(array $result)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        if ($result['errored_operations'] === 0) {
            $this->logger->info(sprintf(
                '[MailChimp][BATCH] Batch has no errored operations, updating export note for all records (no need to fetch detailed results)'
            ));

            $objectIds = array_keys($this->data);
            foreach ($objectIds as $objectId) {
                /** @var CustomerInterface|ElementInterface $customer */
                $customer = Factory::getInstance()->getCustomerProvider()->getById($objectId);
                $remoteId = $apiClient->subscriberHash($this->data[$objectId]['email_address']);

                // add note
                $exportService
                    ->createExportNote($customer, $remoteId)
                    ->save();
            }
        } else {
            // TODO fetch detailed results and handle successful/errored records
        }
    }

    /**
     * @param Batch $batch
     * @param CustomerInterface|ElementInterface $customer
     * @param array $entry
     */
    protected function createBatchOperation(Batch $batch, CustomerInterface $customer, array $entry)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        $objectId  = $customer->getId();
        $remoteId  = $apiClient->subscriberHash($entry['email_address']);

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
     * Export a single entry to mailchimp
     *
     * @param $objectId
     */
    protected function commitSingle($objectId)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        /** @var CustomerInterface|ElementInterface $customer */
        $customer = Factory::getInstance()->getCustomerProvider()->getById($objectId);

        // entry to send to API
        $entry    = $this->buildEntry($customer);
        $remoteId = $apiClient->subscriberHash($entry['email_address']);

        $this->logger->info(sprintf(
            '[MailChimp][CUSTOMER %s] Exporting customer with remote ID %s',
            $objectId,
            $remoteId
        ));

        if ($exportService->wasExported($customer)) {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s] Customer already exists remotely with remote ID %s',
                $objectId,
                $remoteId
            ));
        } else {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s] Customer was not exported yet',
                $objectId
            ));
        }

        // always PUT as API handles both create and update on PUT and we don't need to remember a state
        $result = $apiClient->put(
            $exportService->getListResourceUrl(sprintf('members/%s', $remoteId)),
            $entry
        );

        if ($apiClient->success()) {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s] Export was successful. Remote ID is %s',
                $objectId,
                $remoteId
            ));

            // add note
            $exportService
                ->createExportNote($customer, $result['id'])
                ->save();
        } else {
            $this->logger->error(sprintf(
                '[MailChimp][CUSTOMER %s] Export failed: %s %s',
                $objectId,
                json_encode($apiClient->getLastError()),
                $apiClient->getLastResponse()['body']
            ));
        }
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    protected function buildEntry(CustomerInterface $customer)
    {
        if (!isset($this->data[$customer->getId()])) {
            throw new \RuntimeException(sprintf('Trying to create an entry for customer %d which is not in data set', $customer->getId()));
        }

        // create entry - move merge fields to sub-array
        $entry = $this->transformMergeFields($this->data[$customer->getId()]);

        // add customer segments
        $entry['interests'] = $this->buildCustomerSegmentData($customer);

        return $entry;
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    protected function buildCustomerSegmentData(CustomerInterface $customer)
    {
        $data          = [];
        $exportService = $this->getExportService();

        $customerSegments = [];
        foreach ($customer->getAllSegments() as $customerSegment) {
            $customerSegments[$customerSegment->getId()] = $customerSegment;
        }

        // Mailchimp's API only handles interests which are passed in the request and merges them with existing ones. Therefore
        // we need to pass ALL segments we know and set segments which are not set on the customer as false. Segments
        // which are not set on the customer, but were set before (and are set on Mailchimp's member record) will be kept set
        // if we don't explicitely set them to false.
        foreach ($this->getSegments() as $segment) {
            $remoteSegmentId = $exportService->getRemoteId($segment);

            if (!$exportService->wasExported($segment) || !$remoteSegmentId) {
                $this->logger->error(sprintf(
                    '[MailChimp][CUSTOMER %s] Can not handle segment %s (%s) as is was not exported yet and we don\'t have a remote ID. Please export segments first.',
                    $customer->getId(),
                    $segment->getName(),
                    $segment->getId()
                ));

                continue;
            }

            if (isset($customerSegments[$segment->getId()])) {
                $data[$remoteSegmentId] = true;
            } else {
                $data[$remoteSegmentId] = false;
            }
        }

        return $data;
    }

    /**
     * Transform configured merge fields into merge_fields property
     *
     * @param array $dataRow
     * @return array
     */
    protected function transformMergeFields(array $dataRow)
    {
        $config      = (array)$this->config;
        $mergeFields = (isset($config['merge_fields'])) ? (array)$config['merge_fields'] : [];

        $result = [];
        foreach ($dataRow as $key => $value) {
            if (isset($mergeFields[$key])) {
                $result['merge_fields'][$mergeFields[$key]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        if ($result['merge_fields']) {
            foreach ($result['merge_fields'] as $key => $value) {
                if (null === $value || false === $value) {
                    $result['merge_fields'][$key] = '';
                }
            }
        }

        return $result;
    }

    /**
     * This method is executed before the export is launched.
     * For example it can be used to clean up old export files, start a database transaction, etc.
     * If not needed, just leave the method empty.
     */
    public function setUpExport()
    {
        // noop
    }

    /**
     * This method is executed after all defined attributes of an object are exported.
     * The to-export data is stored in the array $this->data[OBJECT_ID].
     * For example it can be used to write each exported row to a destination database,
     * write the exported entries to a file, etc.
     * If not needed, just leave the method empty.
     *
     * @param AbstractObject|CustomerInterface $object
     */
    public function commitDataRow(AbstractObject $object)
    {
        // noop
    }

    /**
     * This method is executed of an object is not exported (anymore).
     * For example it can be used to remove the entries from a destination database, etc.
     *
     * @param AbstractObject $object
     */
    public function deleteFromExport(AbstractObject $object)
    {
        // noop
    }
}
