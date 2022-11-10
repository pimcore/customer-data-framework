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

use CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor\SegmentBuilderExecutorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildSegmentsCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('cmf:build-segments')
            ->setDescription('Build automatically calculated segments')
            ->addOption(
                'force',
                'f',
                null,
                'force all customers (otherwise only entries from the changes queue will be processed)'
            )
            ->addOption(
                'segmentBuilder',
                's',
                InputOption::VALUE_OPTIONAL,
                'execute segment builder class only (symfony service id of segment builder)'
            )
            ->addOption(
                'customer',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Limit execution to provided customer-id'
            )->addOption(
                'active-only',
                'a',
                InputOption::VALUE_NONE,
                'Limit to active only'
            )->addOption(
                'inactive-only',
                'i',
                InputOption::VALUE_NONE,
                'Limit to in-active only'
            )->addOption(
                'p-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Use custom page-size',
                '500'
            )->addOption(
                'p-start',
                null,
                InputOption::VALUE_OPTIONAL,
                'Start processing at page',
                '1'
            )->addOption(
                'p-end',
                null,
                InputOption::VALUE_OPTIONAL,
                'Stop further processing at page'
            )->addOption(
                'p-amount',
                null,
                InputOption::VALUE_OPTIONAL,
                'Stop further processing after pages'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)//: int
    {
        $customQueue = null;
        if ($input->getOption('customer')) {
            $customQueue = [(int)trim($input->getOption('customer'))];
        }

        $activeState = null;
        if ($this->input->getOption('active-only')) {
            $activeState = true;
        } elseif ($this->input->getOption('inactive-only')) {
            $activeState = false;
        }

        $options = [
            'pageSize' => $this->input->getOption('p-size'),
            'startPage' => $this->input->getOption('p-start'),
            'endPage' => $this->input->getOption('p-end'),
            'pages' => $this->input->getOption('p-amount'),
        ];

        /** @var SegmentBuilderExecutorInterface $segmentBuilderExecutor */
        $segmentBuilderExecutor = \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class);

        $segmentBuilderExecutor->buildCalculatedSegments(
            !$input->getOption('force'),
            $input->getOption('segmentBuilder'),
            $customQueue,
            $activeState,
            $options,
            // capture ctrl+c + kill signal
            true
        );

        return 0;
    }
}
