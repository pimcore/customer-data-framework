<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:37
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\Factory;
use Pimcore\Model\Tool\Lock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActionTriggerQueueCommand extends AbstractCommand {

    const LOCK_KEY = 'cmf_actiontrigger_queue';

    protected function configure()
    {
        $this->setName("cmf:process-actiontrigger-queue")
            ->setDescription("Process entries from action trigger queue")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Pimcore::getDiContainer()->set("CustomerManagementFramework\\Logger", $this->getLogger());

        if(Lock::isLocked(self::LOCK_KEY)) {
            die('locked - not starting now');

        }

        Lock::lock(self::LOCK_KEY);

        try {
            Factory::getInstance()->getActionTriggerQueue()->processQueue();
        } catch(\Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }

        Lock::release(self::LOCK_KEY);

    }

}