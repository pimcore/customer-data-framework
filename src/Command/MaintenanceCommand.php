<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-21
 * Time: 5:12 PM
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\Maintenance\MaintenanceWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceCommand extends AbstractCommand {

    protected function configure() {
        $this->setName('cmf:maintenance')
            ->setDescription("Performs various tasks configured in services.yml -> 'cmf.maintenance.serviceCalls'");
    }

    /**
     * executes the configured MaintenanceWorker service
     * @see MaintenanceWorker
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        \Pimcore::getContainer()->get(MaintenanceWorker::class)->execute();
    }

}