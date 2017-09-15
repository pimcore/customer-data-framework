<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor\SegmentBuilderExecutorInterface;

class MaintenanceEventListener
{
    public function onMaintenance(\Pimcore\Event\System\MaintenanceEvent $e)
    {
        \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class)->executeSegmentBuilderMaintenance();
        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->cleanupExportTmpData();
        \Pimcore::getContainer()->get('cmf.customer_provider.object_naming_scheme')->cleanupEmptyFolders();
    }
}
