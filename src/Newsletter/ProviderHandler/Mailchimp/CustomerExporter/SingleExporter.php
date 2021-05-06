<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
     * @param Mailchimp $mailchimpProviderHandler
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
     * @param Mailchimp $mailchimpProviderHandler
     *
     * @return bool
     */
    public function update(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        $exportService = $mailchimpProviderHandler->getExportService();
        $apiClient = $this->getApiClientFromExportService($exportService);

        // entry to send to API
        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $apiClient->subscriberHash($item->getEmail());

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
            $this->getLogger()->error(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Export failed: %s %s %s',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    json_encode($apiClient->getLastError()),
                    $apiClient->getLastResponse()['body'],
                    implode('; ', $apiClient->getLastRequest())
                ),
                [
                    'relatedObject' => $customer
                ]
            );
        }

        return false;
    }

    /**
     * @param Mailchimp $mailchimpProviderHandler
     * @param MailchimpAwareCustomerInterface $customer
     *
     * @return array|null
     */
    public function fetchCustomer(Mailchimp $mailchimpProviderHandler, MailchimpAwareCustomerInterface $customer)
    {
        $exportService = $mailchimpProviderHandler->getExportService();
        $apiClient = $this->getApiClientFromExportService($exportService);
        $remoteId = $apiClient->subscriberHash($customer->getEmail());

        $result = $apiClient->get(
            $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
        );

        if (!$apiClient->success()) {
            return null;
        }

        return $result;
    }

    protected function handleChangedEmail(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler, $remoteId)
    {
        $exportService = $mailchimpProviderHandler->getExportService();
        $apiClient = $this->getApiClientFromExportService($exportService);

        /* if the email address changes we have to delete and recreate it
           as mailchimp does only allow updating of email addresses for subscribed customers */
        if ($customer->getEmail() != $item->getEmail()) {
            $apiClient->delete(
                $exportService->getListResourceUrl($mailchimpProviderHandler->getListId(), sprintf('members/%s', $remoteId))
            );
            if ($apiClient->success()) {
                $this->getLogger()->notice(
                    sprintf(
                        '[MailChimp][CUSTOMER %s][%s] Deletion and recreation needed as email address changed. Deletion successfull. Remote ID is %s',
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
            $remoteId = $apiClient->subscriberHash($customer->getEmail());
        }

        return $remoteId;
    }

    /**
     * @param MailchimpAwareCustomerInterface $customer
     * @param NewsletterQueueItemInterface $item
     * @param Mailchimp $mailchimpProviderHandler
     */
    protected function delete(MailchimpAwareCustomerInterface $customer, NewsletterQueueItemInterface $item, Mailchimp $mailchimpProviderHandler)
    {
        $exportService = $mailchimpProviderHandler->getExportService();
        $apiClient = $this->getApiClientFromExportService($exportService);

        if ($mailchimpProviderHandler->doesOtherSubscribedCustomerWithEmailExist($customer->getEmail(), $customer->getId())) {
            $this->getLogger()->debug(
                sprintf(
                    '[MailChimp][CUSTOMER %s][%s] Skip deletion of customer as another subscribed customer with email %s exists.',
                    $customer->getId(),
                    $mailchimpProviderHandler->getShortcut(),
                    $customer->getEmail()
                )
            );
            $item->setSuccessfullyProcessed(true);

            return;
        }

        // entry to send to API
        $entry = $mailchimpProviderHandler->buildEntry($customer);
        $remoteId = $apiClient->subscriberHash($item->getEmail());

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
        $exportService = $mailchimpProviderHandler->getExportService();
        $apiClient = $this->getApiClientFromExportService($exportService);

        if ($mailchimpProviderHandler->doesOtherSubscribedCustomerWithEmailExist($item->getEmail(), $item->getCustomerId())) {
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

        // entry to send to API
        $remoteId = $apiClient->subscriberHash($item->getEmail());

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
