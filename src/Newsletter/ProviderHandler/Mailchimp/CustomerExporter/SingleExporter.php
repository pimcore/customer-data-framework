<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp\CustomerExporter;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;

class SingleExporter extends AbstractExporter
{
    /**
     * @var int
     */
    protected $customerId;

    /**
     * Run the actual export
     *
     * @param NewsletterQueueItemInterface $item
     * @param string $listId
     *
     * @return void
     */
    public function export(NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        if($item->getOperation() == NewsletterQueueInterface::OPERATION_UPDATE) {
            /**
             * @var MailchimpAwareCustomerInterface $customer
             */
            $customer = $item->getCustomer();

            if(!$customer->needsExportByNewsletterProviderHandler($mailchimpProviderHandler)) {
                $this->delete($customer, $item, $mailchimpProviderHandler);
                return;
            }

            $this->update($customer, $item, $mailchimpProviderHandler);

        } elseif($item->getOperation() == NewsletterQueueInterface::OPERATION_DELETE) {
            $this->processDeleteQueueItem($item, $mailchimpProviderHandler);
        }
    }

    /**
     * @param MailchimpAwareCustomerInterface $customer
     * @param NewsletterQueueItemInterface $item
     * @param string $listId
     * @return bool
     */
    public function update(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler )
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        // entry to send to API
        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $this->apiClient->subscriberHash($entry['email_address']);

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][CUSTOMER %s] Exporting customer with remote ID %s',
                $customer->getId(),
                $remoteId
            )
        );

        if ($exportService->wasExported($customer, $mailchimpProviderHandler->getListId())) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s] Customer already exists remotely with remote ID %s',
                    $customer->getId(),
                    $remoteId
                )
            );
        } else {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s] Customer was not exported yet',
                    $customer->getId()
                )
            );
        }

        // always PUT as API handles both create and update on PUT and we don't need to remember a state
        $result = $apiClient->put(
            $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId)),
            $entry
        );

        if ($apiClient->success()) {
            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][CUSTOMER %s] Export was successful. Remote ID is %s',
                    $customer->getId(),
                    $remoteId
                ),
                [
                    'relatedObject' => $customer
                ]
            );

            $apiClient->getLastResponse();

            // add note
            $exportService
                ->createExportNote($customer, $mailchimpProviderHandler->getListId(), $result['id'], null, 'Mailchimp Export [' . $mailchimpProviderHandler->getShortcut() . ']', ['exportdataMd5' => $exportService->getMd5($entry)])
                ->save();

            $item->setSuccessfullyProcessed(true);

            $status = isset($entry['status']) ? $entry['status'] : $entry['status_if_new'];
            $mailchimpProviderHandler->updateMailchimpStatus($customer, $status);

            return true;

        } else {
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][CUSTOMER %s] Export failed: %s %s',
                    $customer->getId(),
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body']
                ),
                [
                    'relatedObject' => $customer
                ]
            );
        }

        return false;
    }

    /**
     * @param MailchimpAwareCustomerInterface $customer
     * @param NewsletterQueueItemInterface $item
     * @param string $listId
     */
    protected function delete(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler )
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        // entry to send to API
        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $this->apiClient->subscriberHash($entry['email_address']);

        $this->getLogger()->debug(
            sprintf(
                '[MailChimp][CUSTOMER %s] Deleting customer with remote ID %s',
                $customer->getId(),
                $remoteId
            )
        );

        // always PUT as API handles both create and update on PUT and we don't need to remember a state
        $result = $apiClient->delete(
            $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
        );

        if ($apiClient->success()) {
            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][CUSTOMER %s] Deletion was successful. Remote ID is %s',
                    $customer->getId(),
                    $remoteId
                ),
                [
                    'relatedObject' => $customer
                ]
            );

            // add note
            $exportService
                ->createExportNote($customer, $mailchimpProviderHandler->getListId(), $result['id'], null, 'Mailchimp Deletion [' . $mailchimpProviderHandler->getShortcut() . ']')
                ->save();

            $item->setSuccessfullyProcessed(true);

            $mailchimpProviderHandler->updateMailchimpStatus($customer, null);
        } else {

            $response = $apiClient->getLastResponse();

            if($response['headers']['http_code'] != 404) {
                $this->getLogger()->error(
                    sprintf(
                        '[MailChimp][CUSTOMER %s] Deletion failed: %s %s',
                        $customer->getId(),
                        json_encode($apiClient->getLastError()),
                        $apiClient->getLastResponse()['body']
                    ),
                    [
                        'relatedObject' => $customer
                    ]
                );
            } else {
                $this->getLogger()->debug(
                    sprintf(
                        '[MailChimp][CUSTOMER %s] Deletion not needed as the remote user does not exist. Remote ID is %s',
                        $customer->getId(),
                        $remoteId
                    )
                );
                $item->setSuccessfullyProcessed(true);

                $mailchimpProviderHandler->updateMailchimpStatus($customer, null);
            }


        }
    }

    protected function processDeleteQueueItem(NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler )
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        // entry to send to API
        $remoteId = $this->apiClient->subscriberHash($item->getEmail());

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][CUSTOMER %s] Deleting customer with remote ID %s',
                $item->getCustomerId(),
                $remoteId
            )
        );

        // always PUT as API handles both create and update on PUT and we don't need to remember a state
        $apiClient->delete(
            $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
        );

        if ($apiClient->success()) {
            $this->getLogger()->notice(
                sprintf(
                    '[MailChimp][CUSTOMER %s] Deletion was successful. Remote ID is %s',
                    $item->getCustomerId(),
                    $remoteId
                )
            );

            $item->setSuccessfullyProcessed(true);
        } else {

            $response = $apiClient->getLastResponse();

            if($response['headers']['http_code'] != 404) {
                $this->getLogger()->error(
                    sprintf(
                        '[MailChimp][CUSTOMER %s] Deletion failed: %s %s',
                        $item->getCustomerId(),
                        json_encode($apiClient->getLastError()),
                        $apiClient->getLastResponse()['body']
                    )
                );
            } else {
                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][CUSTOMER %s] Deletion not needed as the remote user does not exist. Remote ID is %s',
                        $item->getCustomerId(),
                        $remoteId
                    )
                );
                $item->setSuccessfullyProcessed(true);
            }


        }
    }
}