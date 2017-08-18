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

use Pimcore\Model\Tool\Lock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActionTriggerQueueCommand extends AbstractCommand
{
    const LOCK_KEY = 'cmf_actiontrigger_queue';

    protected function configure()
    {
        $this->setName('cmf:process-actiontrigger-queue')
            ->setDescription('Process entries from action trigger queue');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (Lock::isLocked(self::LOCK_KEY)) {
            die('locked - not starting now');
        }

        Lock::lock(self::LOCK_KEY);

        try {
            \Pimcore::getContainer()->get('cmf.action_trigger.queue')->processQueue();
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }

        Lock::release(self::LOCK_KEY);
    }
}
