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

use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuplicatesIndexCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('cmf:duplicates-index')
            ->setDescription('Handles the duplicate search index')
            ->addOption('recreate', 'r', null, 'recreate index (total index will be deleted and recreated)')
            ->addOption('calculate', 'c', null, 'calculate potential duplicates')
            ->addOption('analyze', 'a', null, 'analyze false postives (used for calculating potential duplicates)');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = \Pimcore::getContainer()->get('cmf.logger');

        /**
         * @var DuplicatesIndexInterface $duplicatesIndex
         */
        $duplicatesIndex = \Pimcore::getContainer()->get('cmf.customer_duplicates_index');

        if ($input->getOption('analyze')) {
            $duplicatesIndex->setAnalyzeFalsePositives(true);
        } else {
            $duplicatesIndex->setAnalyzeFalsePositives(false);
        }

        if ($input->getOption('recreate')) {
            $logger->notice('start recreate index');
            $duplicatesIndex->recreateIndex();
            $logger->notice('finished recreate index');
        }

        if ($input->getOption('calculate')) {
            $logger->notice('start calculating potential duplicates');
            $duplicatesIndex->calculatePotentialDuplicates($output);
            $logger->notice('finished calculating potential duplicates');
        }
    }
}
