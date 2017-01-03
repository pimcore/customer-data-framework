<?php

use CustomerManagementFramework\Authentication\SsoIdentity\DefaultSsoIdentityService;
use CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\CustomerProvider\DefaultCustomerProvider;
use CustomerManagementFramework\Encryption\DefaultEncryptionService;
use CustomerManagementFramework\Encryption\EncryptionServiceInterface;
use CustomerManagementFramework\ExportToolkit\ExportService\MailChimpExportService;

$config = \CustomerManagementFramework\Plugin::getConfig();

return [

    'CustomerManagementFramework\Logger'
        => reset(\Pimcore\Logger::getLogger()),

    'CustomerManagementFramework\ActivityManager'
        => DI\object('CustomerManagementFramework\ActivityManager\DefaultActivityManager'),

    'CustomerManagementFramework\ActivityStore'
        => DI\object('CustomerManagementFramework\ActivityStore\MariaDb'),

    'CustomerManagementFramework\ActivityView'
        => DI\object('CustomerManagementFramework\ActivityView\DefaultActivityView')
            ->constructor(DI\get('CustomerManagementFramework\View\Formatter')),

    'CustomerManagementFramework\SegmentManager'
        => DI\object('CustomerManagementFramework\SegmentManager\DefaultSegmentManager')
           ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\RESTApi\Export'
        => DI\object('CustomerManagementFramework\RESTApi\Export')
        ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\RESTApi\Import'
        => DI\object('CustomerManagementFramework\RESTApi\Import')
        ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\RESTApi\Update'
        => DI\object('CustomerManagementFramework\RESTApi\Update')
        ->constructor(DI\get('CustomerManagementFramework\Logger')),

    CustomerProviderInterface::class
        => DI\object(DefaultCustomerProvider::class),

    'CustomerManagementFramework\CustomerDuplicatesService'
        => DI\object('CustomerManagementFramework\CustomerDuplicatesService\DefaultCustomerDuplicatesService'),

    'CustomerManagementFramework\CustomerSaveManager'
        => DI\object('CustomerManagementFramework\CustomerSaveManager\DefaultCustomerSaveManager')
           ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\CustomerSaveValidator'
        => DI\object('CustomerManagementFramework\CustomerSaveValidator\DefaultCustomerSaveValidator'),

    'CustomerManagementFramework\CustomerView'
        => DI\object('CustomerManagementFramework\CustomerView\DefaultCustomerView')
            ->constructor(DI\get('CustomerManagementFramework\View\Formatter')),

    'CustomerManagementFramework\CustomerList\ExporterManager'
        => \DI\object('CustomerManagementFramework\CustomerList\ExporterManager'),

    'CustomerManagementFramework\CustomerList\Exporter\Csv'
        => \DI\object('CustomerManagementFramework\CustomerList\Exporter\Csv'),

    'CustomerManagementFramework\ActionTrigger\EventHandler'
        => \DI\object('CustomerManagementFramework\ActionTrigger\EventHandler\DefaultEventHandler')
           ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\ActionTrigger\Queue'
        => \DI\object('CustomerManagementFramework\ActionTrigger\Queue\DefaultQueue')
           ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\ActionTrigger\ActionManager'
        => \DI\object('CustomerManagementFramework\ActionTrigger\ActionManager\DefaultActionManager')
           ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\ActivityUrlTracker'
        => \DI\object('CustomerManagementFramework\ActivityUrlTracker\DefaultActivityUrlTracker')
            ->constructor(DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\View\Formatter'
        => DI\object('CustomerManagementFramework\View\Formatter\DefaultViewFormatter'),

    EncryptionServiceInterface::class
        => \DI\object(DefaultEncryptionService::class)
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    // SSO
    SsoIdentityServiceInterface::class
        => \DI\object(DefaultSsoIdentityService::class)
            ->constructor(DI\get(CustomerProviderInterface::class))
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    'CustomerManagementFramework\Authentication\Sso\HybridAuthHandler'
        => \DI\object('CustomerManagementFramework\Authentication\Sso\DefaultHybridAuthHandler')
            ->constructor(DI\get(SsoIdentityServiceInterface::class), DI\get(EncryptionServiceInterface::class)),

    // MailChimp
    'CustomerManagementFramework\MailChimpClient'
        => \DI\object(\DrewM\MailChimp\MailChimp::class)
            ->constructor($config->MailChimp->apiKey),

    MailChimpExportService::class
        => \DI\object(MailChimpExportService::class)
            ->constructor(DI\get('CustomerManagementFramework\MailChimpClient'), $config->MailChimp->listId)
];
