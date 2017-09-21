<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-21
 * Time: 5:12 PM
 */

namespace CustomerManagementFrameworkBundle\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MaintenanceCommand extends AbstractCommand {

    protected function configure() {
        $this->setName('cmf:maintenance')
            ->setDescription('Performs various minor tasks that need to be executed regularly');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $serviceCalls = \Pimcore::getContainer()->getParameter('cmf.maintenance.serviceCalls');

        foreach($serviceCalls as $service => $call) {
            \Pimcore::getContainer()->get($service)->$call();
        }
    }

}