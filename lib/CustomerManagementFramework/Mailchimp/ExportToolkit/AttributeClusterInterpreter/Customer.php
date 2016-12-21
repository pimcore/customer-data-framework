<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use ExportToolkit\ExportService\AttributeClusterInterpreter\AbstractAttributeClusterInterpreter;
use Pimcore\Model\Object\AbstractObject;

class Customer extends AbstractAttributeClusterInterpreter
{
    /**
     * @return \CustomerManagementFramework\Mailchimp\ExportService
     */
    public function getExportService()
    {
        return Factory::getInstance()->getMailchimpExportService();
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
     * This method is executed after all objects are exported.
     * If not cleaned up in the commitDataRow-method, all exported data is stored in the array $this->data.
     * For example it can be used to write all data to a xml file or commit a database transaction, etc.
     *
     */
    public function commitData()
    {
        if (count($this->data) === 1) {
            $objectId = array_keys($this->data)[0];
            $entry    = $this->transformMergeFields($this->data[$objectId]);

            $this->commitSingle($objectId, $entry);
        } else {
            $this->commitBatch();
        }
    }

    /**
     * Commit a single entry to the API
     *
     * @param $objectId
     * @param array $entry
     */
    protected function commitSingle($objectId, array $entry)
    {
        $exportService = $this->getExportService();
        $apiClient     = $exportService->getApiClient();

        $this->logger->info(sprintf('[MailChimp] Exporting customer %d', $objectId));

        // always call update (PUT), as API handles both create and update on PUT and we don't need to remember a state
        $result = $exportService->update($entry);

        if ($apiClient->success()) {
            $this->logger->info(sprintf('[MailChimp] Success for customer %d', $objectId));

            $customer = Factory::getInstance()->getCustomerProvider()->getById($objectId);

            // add note
            $exportService
                ->createExportNote($customer)
                ->save();

            $this->logger->info(sprintf('[MailChimp] Added export note for customer %d', $objectId));
        } else {
            $this->logger->error(sprintf('[MailChimp] Failed to export customer %d', $objectId));
            $this->logger->error(sprintf('[MailChimp] Error: %s %s', json_encode($apiClient->getLastError()), $apiClient->getLastResponse()['body']));
        }
    }

    protected function commitBatch()
    {
        // TODO
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
}
