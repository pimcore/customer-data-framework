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
        \Pimcore::getDiContainer()->set("CustomerManagementFramework\\Logger", $this->getLogger());

        if($input->getOption("analyze")) {
            Factory::getInstance()->getDuplicatesIndex()->setAnalyzeFalsePositives(true);
        } else {
            Factory::getInstance()->getDuplicatesIndex()->setAnalyzeFalsePositives(false);
        }

        if($input->getOption("recreate")) {
            $this->getLogger()->notice("start recreate index");
            Factory::getInstance()->getDuplicatesIndex()->recreateIndex($this->logger);
            $this->getLogger()->notice("finished recreate index");
        }

        if($input->getOption("calculate")) {
            $this->getLogger()->notice("start calculating potential duplicates");
            Factory::getInstance()->getDuplicatesIndex()->calculatePotentialDuplicates($output);
            $this->getLogger()->notice("finished calculating potential duplicates");
        }
    }

}