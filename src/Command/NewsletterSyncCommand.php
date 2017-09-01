<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use CustomerManagementFrameworkBundle\Newsletter\Manager\NewsletterManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsletterSyncCommand extends AbstractCommand
{
    /**
     * @var NewsletterManagerInterface
     */
    private $newsletterManager;



    protected function configure()
    {
        $this->setName('cmf:newsletter-sync')
            ->setDescription('Handles the synchronization of customers and segments with the newsletter provider');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->newsletterManager = \Pimcore::getContainer()->get(NewsletterManagerInterface::class);

        $this->newsletterManager->syncCustomers();
    }
}
