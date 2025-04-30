<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\ActionTrigger\EventHandler\EventHandlerInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\RuleEnvironment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

class CronTriggerCommand extends AbstractCommand
{
    protected EventHandlerInterface $actionTriggerListener;

    #[Required]
    public function setActionTriggerListener(EventHandlerInterface $actionTriggerListener): void
    {
        $this->actionTriggerListener = $actionTriggerListener;
    }

    protected function configure(): void
    {
        $this->setName('cmf:handle-cron-triggers')
            ->setDescription('Handle cron triggers cronjob - needs to run once per minute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = $this->getLogger();

        $logger->notice('cron trigger');

        $event = new \CustomerManagementFrameworkBundle\ActionTrigger\Event\Cron();
        $environment = new RuleEnvironment();

        $this->actionTriggerListener->handleCustomerListEvent($event, $environment);

        return 0;
    }
}
