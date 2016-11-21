<?php

return [

    'CustomerManagementFramework\Logger' => reset(\Pimcore\Logger::getLogger()),

    'CustomerManagementFramework\ActivityManager' => DI\object('CustomerManagementFramework\ActivityManager\DefaultActivityManager'),

    'CustomerManagementFramework\ActivityStore' => DI\object('CustomerManagementFramework\ActivityStore\MariaDb'),

    'CustomerManagementFramework\ActivityView' => DI\object('CustomerManagementFramework\ActivityView\DefaultActivityView'),

    'CustomerManagementFramework\SegmentManager' => DI\object('CustomerManagementFramework\SegmentManager\DefaultSegmentManager')
                                                    ->constructor(DI\get('CustomerManagementFramework\Logger')),


    'CustomerManagementFramework\RESTApi\Export' => DI\object('CustomerManagementFramework\RESTApi\Export'),

    'CustomerManagementFramework\CustomerSaveManager' => DI\object('CustomerManagementFramework\CustomerSaveManager\DefaultCustomerSaveManager')
                                                         ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\CustomerSaveValidator' => DI\object('CustomerManagementFramework\CustomerSaveValidator\DefaultCustomerSaveValidator'),

];
