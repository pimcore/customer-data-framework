<?php

return [

    'CustomerManagementFramework\Logger' => reset(\Pimcore\Logger::getLogger()),

    'CustomerManagementFramework\ActivityManager' => DI\object('CustomerManagementFramework\ActivityManager\DefaultActivityManager'),

    'CustomerManagementFramework\ActivityStore' => DI\object('CustomerManagementFramework\ActivityStore\MariaDb'),

    'CustomerManagementFramework\ActivityView' => DI\object('CustomerManagementFramework\ActivityView\DefaultActivityView'),

    'CustomerManagementFramework\SegmentManager' => DI\object('CustomerManagementFramework\SegmentManager\DefaultSegmentManager')
                                                    ->constructor(DI\get('CustomerManagementFramework\Logger')),


    'CustomerManagementFramework\RESTApi\Export' => DI\object('CustomerManagementFramework\RESTApi\Export'),

    'CustomerManagementFramework\CustomerDuplicatesService' => DI\object('CustomerManagementFramework\CustomerDuplicatesService\DefaultCustomerDuplicatesService'),

    'CustomerManagementFramework\CustomerSaveManager' => DI\object('CustomerManagementFramework\CustomerSaveManager\DefaultCustomerSaveManager')
                                                         ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\CustomerSaveValidator' => DI\object('CustomerManagementFramework\CustomerSaveValidator\DefaultCustomerSaveValidator'),

    'CustomerManagementFramework\CustomerList\ExporterManager' => \DI\object('CustomerManagementFramework\CustomerList\ExporterManager'),
    'CustomerManagementFramework\CustomerList\Exporter\Csv' => \DI\object('CustomerManagementFramework\CustomerList\Exporter\Csv'),

    'CustomerManagementFramework\ActionTrigger\EventHandler' => \DI\object('CustomerManagementFramework\ActionTrigger\EventHandler\DefaultEventHandler'),

    'CustomerManagementFramework\ActionTrigger\Queue' => \DI\object('CustomerManagementFramework\ActionTrigger\Queue\DefaultQueue')
                                                         ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\ActionTrigger\ActionManager' => \DI\object('CustomerManagementFramework\ActionTrigger\ActionManager\DefaultActionManager')
                                                         ->constructor(DI\get('CustomerManagementFramework\Logger')),


];
