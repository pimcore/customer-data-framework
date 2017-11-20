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

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronTriggerCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('cmf:handle-cron-triggers')
            ->setDescription('Handle cron triggers cronjob - needs to run once per minute');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getLogger();

        $logger->notice('cron trigger');

        $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\Cron();
        $environment = new RuleEnvironment();

        \Pimcore::getContainer()->get('cmf.event_listener.action_trigger')->handleCustomerListEvent($event, $environment);
    }
}
