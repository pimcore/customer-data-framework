<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\SegmentAssignment\Indexer\IndexerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SegmentAssignmentIndexCommand extends AbstractCommand
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * @param IndexerInterface $indexer
     * @required
     */
    public function setIndexer(IndexerInterface $indexer): void
    {
        $this->indexer = $indexer;
    }

    protected function configure()
    {
        $this->setName('cmf:segment-assignment-index')
            ->setDescription('Processes entries from segment assignment queue, use this for manually updating the index, which is usually done during cmf:maintenance');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)//: int
    {
        $this->indexer->processQueue();

        return 0;
    }
}
