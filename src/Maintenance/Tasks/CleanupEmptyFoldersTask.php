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

use Pimcore\Maintenance\TaskInterface;

class CleanupEmptyFoldersTask implements TaskInterface
{
    public function execute(): void
    {
        \Pimcore::getContainer()->get('cmf.customer_provider.object_naming_scheme')->cleanupEmptyFolders();
    }
}
