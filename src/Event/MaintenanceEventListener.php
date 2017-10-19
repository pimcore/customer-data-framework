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

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor\SegmentBuilderExecutorInterface;

class MaintenanceEventListener
{
    public function onMaintenance(\Pimcore\Event\System\MaintenanceEvent $e)
    {
        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->cleanupExportTmpData();
        \Pimcore::getContainer()->get('cmf.customer_provider.object_naming_scheme')->cleanupEmptyFolders();
    }
}
