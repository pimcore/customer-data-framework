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

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\Exception\DuplicateCustomersFoundException;
use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use CustomerManagementFrameworkBundle\Traits\ApplicationLoggerAware;
use Pimcore\Model\User;

class CliSyncProcessor
{
    use ApplicationLoggerAware;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @var UpdateFromMailchimpProcessor
     */
    protected $updateFromMailchimpProcessor;

    /**
     * @var NewsletterManagerInterface
     */
    protected $newsletterManager;

    public function __construct($pimcoreUserName, CustomerProviderInterface $customerProvider, UpdateFromMailchimpProcessor $updateFromMailchimpProcessor, NewsletterManagerInterface $newsletterManager)
    {
        $this->setLoggerComponent('NewsletterSync');

        if (!is_null($pimcoreUserName)) {
            if ($user = User::getByName($pimcoreUserName)) {
                $updateFromMailchimpProcessor->setUser($user);
            } else {
                $this->getLogger()->error(sprintf('pimcore user %s not found (mailchimp config parameter cliUpdatesPimcoreUserName)', $pimcoreUserName));
            }
        }

        $this->customerProvider = $customerProvider;
        $this->updateFromMailchimpProcessor = $updateFromMailchimpProcessor;
        $this->newsletterManager = $newsletterManager;
    }

    public function syncStatusChanges()
    {
        foreach ($this->newsletterManager->getNewsletterProviderHandlers() as $newsletterProviderHandler) {
            if ($newsletterProviderHandler instanceof Mailchimp) {
                $exportService = $newsletterProviderHandler->getExportService();
                $client = $exportService->getApiClient();

                // get updates from the last 30 days
                $date = Carbon::createFromTimestamp(time() - (60 * 60 * 24 * 30));
                $date = $date->toAtomString();

                $count = 20;
                $page = 0;
                while (true) {
                    $result = $client->get(
                        $exportService->getListResourceUrl(
                            $newsletterProviderHandler->getListId(),
                            'members/?since_last_changed='.urlencode($date) . '&count=' . $count . '&offset=' . ($page * $count)
                        )
                    );

                    if ($client->success() && sizeof($result['members'])) {
                        foreach ($result['members'] as $row) {
                            $customer = null;
                            try {
                                if (!$customer = $newsletterProviderHandler->getActiveCustomerByEmail(
                                    $row['email_address']
                                )) {
                                    $this->getLogger()->error(
                                        sprintf('no active customer with email %s found', $row['email_address'])
                                    );
                                }
                            } catch (DuplicateCustomersFoundException $e) {
                                $this->getLogger()->error(
                                    sprintf('multiple active customers with email %s found', $row['email_address'])
                                );
                                continue;
                            } catch (\Exception $e) {
                                $this->getLogger()->error($e->getMessage());
                                continue;
                            }

                            if (!$customer instanceof MailchimpAwareCustomerInterface) {
                                continue;
                            }

                            $status = $row['status'];

                            $statusChanged = $this->updateFromMailchimpProcessor->updateNewsletterStatus(
                                $newsletterProviderHandler,
                                $customer,
                                $status
                            );
                            $mergeFieldsChanged = $this->updateFromMailchimpProcessor->processMergeFields(
                                $newsletterProviderHandler,
                                $customer,
                                $row['merge_fields']
                            );

                            $changed = $statusChanged || $mergeFieldsChanged;

                            if ($changed) {
                                $this->getLogger()->info(
                                    sprintf('customer id %s changed - updating...', $customer->getId())
                                );
                            } else {
                                $this->getLogger()->info(
                                    sprintf('customer id %s did not change - no update needed.', $customer->getId())
                                );
                            }

                            $this->updateFromMailchimpProcessor->saveCustomerIfChanged($customer, $changed);
                        }

                        $page++;
                    } else {
                        break;
                    }
                }
            }
        }
    }

    public function deleteNonExistingItems()
    {
        foreach ($this->newsletterManager->getNewsletterProviderHandlers() as $newsletterProviderHandler) {
            if ($newsletterProviderHandler instanceof Mailchimp) {
                $exportService = $newsletterProviderHandler->getExportService();
                $client = $exportService->getApiClient();

                $count = 20;
                $page = 0;
                while (true) {
                    $url = $exportService->getListResourceUrl($newsletterProviderHandler->getListId(), 'members/?count=' . $count . '&offset=' . ($page * $count));
                    $result = $client->get(
                        $url
                    );

                    if ($client->success() && sizeof($result['members'])) {
                        foreach ($result['members'] as $row) {
                            $list = $this->customerProvider->getList();
                            $list->setCondition('email = ?', $row['email_address']);

                            $this->getLogger()->info(sprintf('check email %s', $row['email_address']));
                            if ($list->count()) {
                                continue;
                            }

                            if ($row['status'] === Mailchimp::STATUS_UNSUBSCRIBED || $row['status'] === Mailchimp::STATUS_CLEANED) {
                                continue;
                            }

                            $remoteId = $client->subscriberHash($row['email_address']);

                            $this->getLogger()->notice(
                                sprintf(
                                    '[MailChimp][CUSTOMER %s][%s] Delete email in mailchimp. Remote ID is %s',
                                    $row['email_address'],
                                    $newsletterProviderHandler->getShortcut(),
                                    $remoteId
                                )
                            );

                            $client->delete(
                                $exportService->getListResourceUrl($newsletterProviderHandler->getListId(), sprintf('members/%s', $remoteId))
                            );

                            if ($client->success()) {
                                $this->getLogger()->notice(
                                    sprintf(
                                        '[MailChimp][CUSTOMER %s][%s] Deletion was successful. Remote ID is %s',
                                        $row['email_address'],
                                        $newsletterProviderHandler->getShortcut(),
                                        $remoteId
                                    )
                                );
                            } else {
                                $this->getLogger()->error(
                                    sprintf(
                                        '[MailChimp][CUSTOMER %s][%s] Deletion failed. Remote ID is %s',
                                        $row['email_address'],
                                        $newsletterProviderHandler->getShortcut(),
                                        $remoteId
                                    )
                                );
                            }
                        }
                        $page++;
                    } else {
                        if (!$client->success()) {
                            $this->getLogger()->error(
                                'get members failed: ' . $url
                            );
                        }
                        break;
                    }
                }
            }
        }
    }
}
