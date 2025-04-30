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

use CustomerManagementFrameworkBundle\Maintenance\MaintenanceWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Service\Attribute\Required;

class MaintenanceCommand extends AbstractCommand
{
    protected MaintenanceWorker $maintenanceWorker;

    #[Required]
    public function setMaintenanceWorker(MaintenanceWorker $maintenanceWorker): void
    {
        $this->maintenanceWorker = $maintenanceWorker;
    }

    protected function configure(): void
    {
        $this->setName('cmf:maintenance')
            ->setDescription("Performs various tasks configured in services.yml -> 'cmf.maintenance.serviceCalls'");
    }

    /**
     * executes the configured MaintenanceWorker service
     *
     * @see MaintenanceWorker
     *
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->maintenanceWorker->execute();

        return 0;
    }
}
