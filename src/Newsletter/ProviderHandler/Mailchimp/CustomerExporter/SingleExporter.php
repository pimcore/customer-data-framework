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
        if ($item->getOperation() == NewsletterQueueInterface::OPERATION_UPDATE) {
            /**
             * @var MailchimpAwareCustomerInterface $customer
             */
            $customer = $item->getCustomer();

            if (!$customer->needsExportByNewsletterProviderHandler($mailchimpProviderHandler)) {
                $this->delete($customer, $item, $mailchimpProviderHandler);

                return;
            }

            $this->update($customer, $item, $mailchimpProviderHandler);
        } elseif ($item->getOperation() == NewsletterQueueInterface::OPERATION_DELETE) {
            $this->processDeleteQueueItem($item, $mailchimpProviderHandler);
        }
    }

    /**
     * @param MailchimpAwareCustomerInterface $customer
     * @param NewsletterQueueItemInterface $item
     * @param string $listId
     *
     * @return bool
     */
    public function update(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        // entry to send to API
        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $this->apiClient->subscriberHash($item->getEmail());

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][CUSTOMER %s][%s] Exporting customer with remote ID %s',
                $customer->getId(),
                $mailchimpProviderHandler->getShortcut(),
                $remoteId
            )
        );

        $remoteId = $this->handleChangedEmail($customer, $item, $mailchimpProviderHandler, $remoteId);

        if ($exportService->wasExported($customer, $mailchimpProviderHandler->getListId())) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Customer already exists remotely with remote ID %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId
                )
            );
        } else {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Customer was not exported yet',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut()
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
                    '[MailChimp][CUSTOMER %s][%s] Export was successful. Remote ID is %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
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
            p_r($entry);

            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Export failed: %s %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
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

    protected function handleChangedEmail(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler, $remoteId)
    {
        $exportService = $this->exportService;
        $apiClient = $exportService->getApiClient();

        /* if the email address changes and the customer is not subscribed in mailchimp we have to delete and recreate it
           as mailchimp does only allow updating of email addresses for subscribed customers */
        if ($customer->getEmail() != $item->getEmail() && $mailchimpProviderHandler->getMailchimpStatus($customer) != Mailchimp::STATUS_SUBSCRIBED) {
            $apiClient->delete(
                $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
            );
            if ($apiClient->success()) {
                $this->getLogger()->notice(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion and recreation needed as email address changed and the status was not "subscribed". Deletion successfull. Remote ID is %s',
                        $customer->getId(),
                        $mailchimpProviderHandler->getShortcut(),
                        $remoteId
                    ),
                    [
                        'relatedObject' => $customer
                    ]
                );
            } elseif ($apiClient->getLastResponse()['headers']['http_code'] == 404) {
                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion and recreation needed as email address changed and the status was not "subscribed". Remote ID is %s. Deletion skipped as it already was done before.',
                        $customer->getId(),
                        $mailchimpProviderHandler->getShortcut(),
                        $remoteId
                    ),
                    [
                        'relatedObject' => $customer
                    ]
                );
            } else {
                $this->getLogger()->error(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion and recreation needed as email address changed and the status was not "subscribed". Remote ID is %s. Deletion failed: %s %s',
                        $customer->getId(),
                        $mailchimpProviderHandler->getShortcut(),
                        $remoteId,
                        json_encode($apiClient->getLastError()),
                        $apiClient->getLastResponse()['body']
                    ),
                    [
                        'relatedObject' => $customer
                    ]
                );

                return false;
            }

            // change the remote id to the new/current email address => recreate the customer with the new email/ID
            $remoteId = $this->apiClient->subscriberHash($customer->getEmail());
        } elseif ($customer->getEmail() != $item->getEmail()) {
            $this->getLogger()->info(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Check if subscriber with old email exists. Remote ID is %s.',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId
                ),
                [
                    'relatedObject' => $customer
                ]
            );

            $apiClient->get(
                $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
            );

            /* if the old email does not exist anymore it was already deleted or updated previously
               => move to the current email/ID and update the customer with the current ID */
            if (!$apiClient->success() && $apiClient->getLastResponse()['headers']['http_code'] == 404) {
                $remoteId = $this->apiClient->subscriberHash($customer->getEmail());

                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Subscriber with old email does not exist anymore. Create/update subscriber with new email. Remote ID is %s.',
                        $customer->getId(),
                        $mailchimpProviderHandler->getShortcut(),
                        $remoteId
                    ),
                    [
                        'relatedObject' => $customer
                    ]
                );
            }
        }

        return $remoteId;
    }

    /**
     * @param MailchimpAwareCustomerInterface $customer
     * @param NewsletterQueueItemInterface $item
     * @param string $listId
     */
    protected function delete(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        // entry to send to API
        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $this->apiClient->subscriberHash($item->getEmail());

        $this->getLogger()->debug(
            sprintf(
                '[MailChimp][CUSTOMER %s][%s] Deleting customer with remote ID %s',
                $customer->getId(),
                $mailchimpProviderHandler->getShortcut(),
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
                    '[MailChimp][CUSTOMER %s][%s] Deletion was successful. Remote ID is %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
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

            if ($response['headers']['http_code'] != 404) {
                $this->getLogger()->error(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion failed: %s %s',
                        $customer->getId(),
                        $mailchimpProviderHandler->getShortcut(),
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
                        '[MailChimp][CUSTOMER %s][%s] Deletion not needed as the remote user does not exist. Remote ID is %s',
                        $customer->getId(),
                        $mailchimpProviderHandler->getShortcut(),
                        $remoteId
                    )
                );
                $item->setSuccessfullyProcessed(true);

                $mailchimpProviderHandler->updateMailchimpStatus($customer, null);
            }
        }
    }

    protected function processDeleteQueueItem(NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        $exportService = $this->exportService;
        $apiClient = $this->apiClient;

        // entry to send to API
        $remoteId = $this->apiClient->subscriberHash($item->getEmail());

        $this->getLogger()->info(
            sprintf(
                '[MailChimp][CUSTOMER %s][%s] Deleting customer with remote ID %s',
                $item->getCustomerId(),
                $mailchimpProviderHandler->getShortcut(),
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
                    '[MailChimp][CUSTOMER %s][%s] Deletion was successful. Remote ID is %s',
                    $item->getCustomerId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $remoteId
                )
            );

            $item->setSuccessfullyProcessed(true);
        } else {
            $response = $apiClient->getLastResponse();

            if ($response['headers']['http_code'] != 404) {
                $this->getLogger()->error(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion failed: %s %s',
                        $item->getCustomerId(),
                        $mailchimpProviderHandler->getShortcut(),
                        json_encode($apiClient->getLastError()),
                        $apiClient->getLastResponse()['body']
                    )
                );
            } else {
                $this->getLogger()->info(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion not needed as the remote user does not exist. Remote ID is %s',
                        $item->getCustomerId(),
                        $mailchimpProviderHandler->getShortcut(),
                        $remoteId
                    )
                );
                $item->setSuccessfullyProcessed(true);
            }
        }
    }
}
