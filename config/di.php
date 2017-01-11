<?php

use CustomerManagementFramework\Authentication\SsoIdentity\DefaultSsoIdentityService;
use CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\CustomerProvider\DefaultCustomerProvider;
use CustomerManagementFramework\Encryption\DefaultEncryptionService;
use CustomerManagementFramework\Encryption\EncryptionServiceInterface;
use CustomerManagementFramework\ExportToolkit\ExportService\MailChimpExportService;
use CustomerManagementFramework\RESTApi\CustomersHandler;
use CustomerManagementFramework\RESTApi\ActivitiesHandler;
use CustomerManagementFramework\RESTApi\SegmentsHandler;
use CustomerManagementFramework\RESTApi\SegmentGroupsHandler;
use CustomerManagementFramework\RESTApi\SegmentsOfCustomerHandler;
use CustomerManagementFramework\RESTApi\DeletionsHandler;
use Interop\Container\ContainerInterface;
use Pimcore\View\Helper\Url;

$config = \CustomerManagementFramework\Plugin::getConfig();

return [
    // parameters/values
    'cmf.rest.customers.route'          => 'cmf-rest-customers',
    'cmf.rest.customers.resource-route' => 'cmf-rest-customers-resource',
    'cmf.rest.customers.prefix'         => '/cmf/api/customers',

    'cmf.rest.activities.route'          => 'cmf-rest-activities',
    'cmf.rest.activities.resource-route' => 'cmf-rest-activities-resource',
    'cmf.rest.activities.prefix'         => '/cmf/api/activities',

    'cmf.rest.segments.prefix'          => '/cmf/api/segments',
    'cmf.rest.segments.route'          => 'cmf-rest-segments',
    'cmf.rest.segments.resource-route' => 'cmf-rest-segments-resource',

    'cmf.rest.segment-groups.prefix'          => '/cmf/api/segment-groups',
    'cmf.rest.segment-groups.route'          => 'cmf-rest-segment-groups',
    'cmf.rest.segment-groups.resource-route' => 'cmf-rest-segment-groups-resource',

    'cmf.rest.segments-of-customer.prefix'          => '/cmf/api/segments-of-customer',

    'cmf.rest.deletions.prefix'          => '/cmf/api/deletions',

    // pimcore URL view helper - TODO move this to core?
    Url::class => function(ContainerInterface $container) {
        /** @var \Pimcore\Controller\Action\Helper\ViewRenderer $broker */
        $broker = \Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');

        /** @var \Pimcore\View $view */
        $view = $broker->view;

        if ($view) {
            return $view->getHelper('url');
        }

        return null;
    },

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


    CustomersHandler::class
        => DI\object(CustomersHandler::class)
            ->constructor(DI\get(CustomerProviderInterface::class))
            ->method('setPathPrefix', DI\get('cmf.rest.customers.prefix'))
            ->method('setApiRoute', DI\get('cmf.rest.customers.route'))
            ->method('setApiResourceRoute', DI\get('cmf.rest.customers.resource-route'))
            ->method('setUrlHelper', DI\get(Url::class))
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    ActivitiesHandler::class
        => DI\object()
            ->method('setPathPrefix', DI\get('cmf.rest.activities.prefix'))
            ->method('setApiRoute', DI\get('cmf.rest.activities.route'))
            ->method('setApiResourceRoute', DI\get('cmf.rest.activities.resource-route'))
            ->method('setUrlHelper', DI\get(Url::class))
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    SegmentsHandler::class
        => DI\object(SegmentsHandler::class)
            ->method('setPathPrefix', DI\get('cmf.rest.segments.prefix'))
            ->method('setApiRoute', DI\get('cmf.rest.segments.route'))
            ->method('setApiResourceRoute', DI\get('cmf.rest.segments.resource-route'))
            ->method('setUrlHelper', DI\get(Url::class))
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    SegmentGroupsHandler::class
        => DI\object(SegmentGroupsHandler::class)
            ->method('setPathPrefix', DI\get('cmf.rest.segment-groups.prefix'))
            ->method('setApiRoute', DI\get('cmf.rest.segment-groups.route'))
            ->method('setApiResourceRoute', DI\get('cmf.rest.segment-groups.resource-route'))
            ->method('setUrlHelper', DI\get(Url::class))
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    SegmentsOfCustomerHandler::class
        => DI\object(SegmentsOfCustomerHandler::class)
            ->method('setPathPrefix', DI\get('cmf.rest.segments-of-customer.prefix'))
            ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

    DeletionsHandler::class
    => DI\object(DeletionsHandler::class)
        ->method('setPathPrefix', DI\get('cmf.rest.deletions.prefix'))
        ->method('setLogger', DI\get('CustomerManagementFramework\Logger')),

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
