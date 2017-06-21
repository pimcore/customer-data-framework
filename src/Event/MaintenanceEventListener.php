<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 16/06/2017
 * Time: 10:55
 */

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\Model\AbstractObjectActivity;
use CustomerManagementFrameworkBundle\Model\ActivityInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Event\Model\ObjectEvent;
use Pimcore\Model\Object\ActivityDefinition;

class MaintenanceEventListener {

    public function onMaintenance( \Pimcore\Event\System\MaintenanceEvent $e)
    {
        \Pimcore::getContainer()->get('cmf.segment_manager')->executeSegmentBuilderMaintenance();
    }
}