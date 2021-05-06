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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use Psr\Log\LoggerInterface;

class WebhookProcessor
{
    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var UpdateFromMailchimpProcessor
     */
    protected $updateFromMailchimpProcessor;

    public function __construct(CustomerProviderInterface $customerProvider, UpdateFromMailchimpProcessor $updateFromMailchimpProcessor)
    {
        $this->customerProvider = $customerProvider;
        $this->updateFromMailchimpProcessor = $updateFromMailchimpProcessor;
    }

    public function process(Mailchimp $mailchimpHandler, array $webhookData, LoggerInterface $logger)
    {
        $logger->info($webhookData['data']['email']);

        try {
            if (!$customer = $mailchimpHandler->getActiveCustomerByEmail($webhookData['data']['email'])) {
                $logger->error(sprintf('no active customer with email %s found', $webhookData['data']['email']));

                return true;
            }

            $logger->info((string) $customer);
        } catch (\RuntimeException $e) {
            $logger->error(sprintf('multiple active customers with email %s found', $webhookData['data']['email']));

            return true;
        }

        $logger->info(sprintf('process web hook data for provider handler with list id %s and customer ID %s', $mailchimpHandler->getListId(), $customer->getId()));

        /**
         * @var MailchimpAwareCustomerInterface $customer
         * $customer
         */
        $changed = false;

        if ($webhookData['type'] == 'subscribe') {
            $changed = $this->processSubscribe($mailchimpHandler, $customer, $logger);
        } elseif ($webhookData['type'] == 'unsubscribe') {
            $changed = $this->processUnsubscribe($mailchimpHandler, $customer, $logger);
        } elseif ($webhookData['type'] == 'cleaned') {
            $changed = $this->processCleaned($mailchimpHandler, $customer, $logger);
        } elseif ($webhookData['type'] == 'profile') {
            $changed = $this->processProfile($mailchimpHandler, $customer, $webhookData, $logger);
        } else {
            $logger->error(sprintf('webhook type %s currently not implemented', $webhookData['type']));
        }

        $this->updateFromMailchimpProcessor->saveCustomerIfChanged($customer, $changed);
    }

    protected function processSubscribe(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, LoggerInterface $logger)
    {
        $logger->info(sprintf("process 'subscribe' web hook data for provider handler with list id %s and customer ID %s", $mailchimpHandler->getListId(), $customer->getId()));

        return $this->updateFromMailchimpProcessor->updateNewsletterStatus($mailchimpHandler, $customer, Mailchimp::STATUS_SUBSCRIBED);
    }

    protected function processUnsubscribe(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, LoggerInterface $logger)
    {
        $logger->info(sprintf("process 'unsubscribe' web hook data for provider handler with list id %s and customer ID %s", $mailchimpHandler->getListId(), $customer->getId()));

        return $this->updateFromMailchimpProcessor->updateNewsletterStatus($mailchimpHandler, $customer, Mailchimp::STATUS_UNSUBSCRIBED);
    }

    protected function processCleaned(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, LoggerInterface $logger)
    {
        $logger->info(sprintf("process 'cleaned' web hook data for provider handler with list id %s and customer ID %s", $mailchimpHandler->getListId(), $customer->getId()));

        return $this->updateFromMailchimpProcessor->updateNewsletterStatus($mailchimpHandler, $customer, Mailchimp::STATUS_CLEANED);
    }

    protected function processProfile(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, array $webhookData, LoggerInterface $logger)
    {
        $logger->info(sprintf("process 'profile' web hook data for provider handler with list id %s and customer ID %s", $mailchimpHandler->getListId(), $customer->getId()));

        return $this->updateFromMailchimpProcessor->processMergeFields($mailchimpHandler, $customer, $webhookData['data']['merges']);
    }
}
