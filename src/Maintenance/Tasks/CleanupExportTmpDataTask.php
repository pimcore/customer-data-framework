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

namespace CustomerManagementFrameworkBundle\Maintenance\Tasks;

use CustomerManagementFrameworkBundle\CustomerList\ExporterManagerInterface;
use Pimcore\Maintenance\TaskInterface;

class CleanupExportTmpDataTask implements TaskInterface
{
    public function __construct(private ExporterManagerInterface $exporterManager)
    {
    }

    public function execute(): void
    {
        $this->exporterManager->cleanupExportTmpData();
    }
}
