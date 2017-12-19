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

use CustomerManagementFrameworkBundle\SegmentAssignment\Indexer\IndexerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SegmentAssignmentIndexCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('cmf:segment-assignment-index')
            ->setDescription('Processes entries from segment assignment queue, use this for manually updating the index, which is usually done during cmf:maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get(IndexerInterface::class)->processQueue();
    }
}
