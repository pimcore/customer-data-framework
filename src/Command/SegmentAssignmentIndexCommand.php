<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Command;

use CustomerManagementFrameworkBundle\SegmentAssignment\Indexer\IndexerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

class SegmentAssignmentIndexCommand extends AbstractCommand
{
    protected IndexerInterface $indexer;

    #[Required]
    public function setIndexer(IndexerInterface $indexer): void
    {
        $this->indexer = $indexer;
    }

    protected function configure(): void
    {
        $this->setName('cmf:segment-assignment-index')
            ->setDescription('Processes entries from segment assignment queue, use this for manually updating the index, which is usually done during cmf:maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->indexer->processQueue();

        return 0;
    }
}
