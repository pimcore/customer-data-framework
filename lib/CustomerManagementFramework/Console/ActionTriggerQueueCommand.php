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

class ActionTriggerQueueCommand extends AbstractCommand {

    protected function configure()
    {
        $this->setName("cmf:process-actiontrigger-queue")
            ->setDescription("Process entries from action trigger queue")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Pimcore::getDiContainer()->set("CustomerManagementFramework\\Logger", $this->getLogger());

        Factory::getInstance()->getActionTriggerQueue()->processQueue();
    }

}