<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:37
 */

namespace CustomerManagementFrameworkBundle\Command;

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

        \Pimcore::getContainer()->get('cmf.event_listener.action_trigger')->handleCustomerListEvent($event);
    }
}
