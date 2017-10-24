<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 23.10.2017
 * Time: 12:21
 */

namespace CustomerManagementFrameworkBundle\Command;


use CustomerManagementFrameworkBundle\SegmentAssignment\Indexer\IndexerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SegmentAssignmentIndexCommand extends AbstractCommand {
    protected function configure() {
        $this->setName('cmf:segment-assignment-index')
            ->setDescription('Processes entries from segment assignment queue, use this for manually updating the index, which is usually done during cmf:maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get(IndexerInterface::class)->processQueue();
    }
}