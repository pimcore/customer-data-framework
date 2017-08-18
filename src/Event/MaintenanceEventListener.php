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

class MaintenanceEventListener
{
    public function onMaintenance(\Pimcore\Event\System\MaintenanceEvent $e)
    {
        \Pimcore::getContainer()->get('cmf.segment_manager')->executeSegmentBuilderMaintenance();
        \Pimcore::getContainer()->get('cmf.customer_exporter_manager')->cleanupExportTmpData();
    }
}
