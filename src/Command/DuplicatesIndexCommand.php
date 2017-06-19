<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:37
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use CustomerManagementFrameworkBundle\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuplicatesIndexCommand extends AbstractCommand {

    protected function configure()
    {
        $this->setName("cmf:duplicates-index")
            ->setDescription("Handles the duplicate search index")
            ->addOption("recreate", "r", null, "recreate index (total index will be deleted and recreated)")
            ->addOption("calculate", "c", null, "calculate potential duplicates")
            ->addOption("analyze", "a", null, "analyze false postives (used for calculating potential duplicates)")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = \Pimcore::getContainer()->get('cmf.logger');

        /**
         * @var DuplicatesIndexInterface $duplicatesIndex
         */
        $duplicatesIndex = \Pimcore::getContainer()->get('cmf.customer_duplicates_index');

        if($input->getOption("analyze")) {
            $duplicatesIndex->setAnalyzeFalsePositives(true);
        } else {
            $duplicatesIndex->setAnalyzeFalsePositives(false);
        }

        if($input->getOption("recreate")) {
            $logger->notice("start recreate index");
            $duplicatesIndex->recreateIndex();
            $logger->notice("finished recreate index");
        }

        if($input->getOption("calculate")) {
            $logger->notice("start calculating potential duplicates");
            $duplicatesIndex->calculatePotentialDuplicates($output);
            $logger->notice("finished calculating potential duplicates");
        }
    }

}