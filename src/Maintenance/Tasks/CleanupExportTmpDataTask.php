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
