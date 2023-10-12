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

namespace CustomerManagementFrameworkBundle\Newsletter\Command;

use CustomerManagementFrameworkBundle\Command\AbstractCommand;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\DefaultNewsletterQueueItem;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewsletterSyncCommand extends AbstractCommand
{
    use LockableTrait;

    /**
     * @var NewsletterManagerInterface
     */
    protected $newsletterManager;

    /**
     * @var NewsletterQueueInterface
     */
    protected $newsletterQueue;

    /**
     * @var CustomerProviderInterface
     */
    protected $customerProvider;

    /**
     * @param NewsletterManagerInterface $newsletterManager
     * @param NewsletterQueueInterface $newsletterQueue
     * @param CustomerProviderInterface $customerProvider
     */
    public function __construct(NewsletterManagerInterface $newsletterManager, NewsletterQueueInterface $newsletterQueue, CustomerProviderInterface $customerProvider)
    {
        parent::__construct();
        $this->newsletterManager = $newsletterManager;
        $this->newsletterQueue = $newsletterQueue;
        $this->customerProvider = $customerProvider;
    }

    protected function configure()
    {
        $this->setName('cmf:newsletter-sync')
            ->setDescription('Handles the synchronization of customers and segments with the newsletter provider')
            ->addOption('customer-data-sync', 'c', null, 'process customer data sync')
            ->addOption('enqueue-all-customers', null, null, 'add all customers to newsletter queue')
            ->addOption('all-customers', 'a', null, 'full sync of all customers (otherwise only the newsletter queue will be processed)')
            ->addOption('force-segments', 's', null, 'force update of segments (otherwise only changed segments will be exported)')
            ->addOption('force-customers', 'f', null, 'force update of customers (otherwise only changed customers will be exported)')
            ->addOption('mailchimp-status-sync', 'm', null, 'mailchimp status sync (direction mailchimp => pimcore) for all mailchimp newsletter provider handlers')
            ->addOption('delete-non-existing-items-in-mailchimp', null, null, 'delete email addresses in mailchimp which do not exist in the CMF database any more (only for maintenance purpose, should not be needed when the system is running regularly)')
            ->addOption('process-queue-item', null, InputOption::VALUE_REQUIRED, 'process single queue item (provide json data of queue item)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('enqueue-all-customers')) {
            $this->newsletterQueue->enqueueAllCustomers();
        }

        if ($input->getOption('force-segments')) {
            $this->newsletterManager->syncSegments(true);
        }

        if ($input->getOption('customer-data-sync') || $input->getOption('all-customers')) {
            $lockKey = 'plugin_cmf_newsletter_sync_queue';

            if (!$this->lock($lockKey)) {
                $output->writeln('The command is already running in another process.');

                return 0;
            }

            if (!$input->getOption('force-segments')) {
                $this->newsletterManager->syncSegments();
            }

            $this->newsletterManager->syncCustomers(
                (bool)$input->getOption('all-customers'),
                (bool)$input->getOption('force-customers')
            );

            $this->release();
        }

        if ($input->getOption('mailchimp-status-sync')) {
            $this->mailchimpStatusSync();
        }

        if ($input->getOption('delete-non-existing-items-in-mailchimp')) {
            $this->deleteNonExistingItemsInMailchimp();
        }

        if ($processQueueItem = $input->getOption('process-queue-item')) {
            $data = json_decode($processQueueItem, true);

            if (empty($data['customerId']) || empty($data['email']) || empty($data['operation']) || empty($data['modificationDate'])) {
                throw new \Exception('invalid item');
            }

            $item = new DefaultNewsletterQueueItem($data['customerId'], $this->customerProvider->getById($data['customerId']), $data['email'], $data['operation'], $data['modificationDate']);
            $this->newsletterManager->syncSingleCustomerQueueItem($item);
        }

        return 0;
    }

    protected function mailchimpStatusSync()
    {
        /**
         * @var Mailchimp\CliSyncProcessor $cliSyncProcessor
         */
        $cliSyncProcessor = \Pimcore::getContainer()->get(Mailchimp\CliSyncProcessor::class);

        $cliSyncProcessor->syncStatusChanges();
    }

    protected function deleteNonExistingItemsInMailchimp()
    {
        /**
         * @var Mailchimp\CliSyncProcessor $cliSyncProcessor
         */
        $cliSyncProcessor = \Pimcore::getContainer()->get(Mailchimp\CliSyncProcessor::class);

        $cliSyncProcessor->deleteNonExistingItems();
    }
}
