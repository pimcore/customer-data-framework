<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:37
 */

namespace CustomerManagementFramework\Console;

use CustomerManagementFramework\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronTriggerCommand extends AbstractCommand {

    protected function configure()
    {
        $this->setName("cmf:handle-cron-triggers")
            ->setDescription("Handle cron triggers cronjob - needs to run once per minute")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getLogger();
        \Pimcore::getDiContainer()->set("CustomerManagementFramework\\Logger", $logger);

        $logger->notice('cron trigger');

        $event = new \CustomerManagementFramework\ActionTrigger\Event\Cron();

        $e = new \Zend_EventManager_Event;
        Factory::getInstance()->getActionTriggerEventHandler()->handleCustomerListEvent($e, $event);
    }

}