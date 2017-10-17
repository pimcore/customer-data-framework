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

use CustomerManagementFrameworkBundle\Maintenance\MaintenanceWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('cmf:maintenance')
            ->setDescription("Performs various tasks configured in services.yml -> 'cmf.maintenance.serviceCalls'");
    }

    /**
     * executes the configured MaintenanceWorker service
     *
     * @see MaintenanceWorker
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Pimcore::getContainer()->get(MaintenanceWorker::class)->execute();
    }
}
