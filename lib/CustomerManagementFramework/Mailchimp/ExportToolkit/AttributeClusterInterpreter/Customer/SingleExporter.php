<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter\Customer;

use CustomerManagementFramework\Mailchimp\ExportToolkit\AttributeClusterInterpreter\Customer;

class SingleExporter extends AbstractExporter
{
    /**
     * @var int
     */
    protected $customerId;

    /**
     * @param Customer $interpreter
     * @param int $customerId
     */
    public function __construct(Customer $interpreter, $customerId)
    {
        parent::__construct($interpreter);

        $this->customerId = $customerId;
    }

    /**
     * Run the actual export
     */
    public function export()
    {
        $exportService = $this->exportService;
        $apiClient     = $this->apiClient;

        $customer = $this->getCustomer($this->customerId);

        // entry to send to API
        $entry    = $this->interpreter->buildEntry($customer);
        $remoteId = $this->apiClient->subscriberHash($entry['email_address']);

        $this->logger->info(sprintf(
            '[MailChimp][CUSTOMER %s] Exporting customer with remote ID %s',
            $this->customerId,
            $remoteId
        ));

        if ($exportService->wasExported($customer)) {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s] Customer already exists remotely with remote ID %s',
                $this->customerId,
                $remoteId
            ));
        } else {
            $this->logger->info(sprintf(
                '[MailChimp][CUSTOMER %s] Customer was not exported yet',
                $this->customerId
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
                $this->customerId,
                $remoteId
            ));

            // add note
            $exportService
                ->createExportNote($customer, $result['id'])
                ->save();
        } else {
            $this->logger->error(sprintf(
                '[MailChimp][CUSTOMER %s] Export failed: %s %s',
                $this->customerId,
                json_encode($apiClient->getLastError()),
                $apiClient->getLastResponse()['body']
            ));
        }
    }
}
