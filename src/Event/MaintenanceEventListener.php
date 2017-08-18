<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 16/06/2017
 * Time: 10:55
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
