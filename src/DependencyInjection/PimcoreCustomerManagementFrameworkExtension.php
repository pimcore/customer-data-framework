<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DependencyInjection;

use CustomerManagementFrameworkBundle\CustomerDuplicatesService\CustomerDuplicatesServiceInterface;
use CustomerManagementFrameworkBundle\CustomerMerger\CustomerMergerInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme\ObjectNamingSchemeInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\CustomerSaveValidatorInterface;
use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class PimcoreCustomerManagementFrameworkExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');
        $loader->load('services_templating.yml');
        $loader->load('services_events.yml');

        $loader->load('services_security.yml');

        if ($config['oauth_client']['enabled']) {
            $loader->load('services_security_oauth_client.yml');
        }


        if($config['newsletter']['newsletterSyncEnabled']) {
            $loader->load('services_newsletter.yml');
        }

        $this->registerGeneralConfiguration($container, $config['general']);
        $this->registerEncryptionConfiguration($container, $config['encryption']);
        $this->registerCustomerSaveManagerConfiguration($container, $config['customer_save_manager']);
        $this->registerCustomerSaveValidatorConfiguration($container, $config['customer_save_validator']);
        $this->registerSegmentManagerConfiguration($container, $config['segment_manager']);
        $this->registerCustomerProviderConfiguration($container, $config['customer_provider']);
        $this->registerCustomerListConfiguration($container, $config['customer_list']);
        $this->registerCustomerDuplicatesServicesConfiguration($container, $config['customer_duplicates_services']);
        $this->registerNewsletterConfiguration($container, $config['newsletter']);
    }

    private function registerGeneralConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('pimcore_customer_management_framework.general.customerPimcoreClass', $config['customerPimcoreClass']);
        $container->setParameter('pimcore_customer_management_framework.general.mailBlackListFile', $config['mailBlackListFile']);
    }

    private function registerEncryptionConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('pimcore_customer_management_framework.encryption.secret', $config['secret']);
    }

    private function registerCustomerSaveManagerConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setAlias('cmf.customer_save_manager', CustomerSaveManagerInterface::class);

        $container->setParameter('pimcore_customer_management_framework.customer_save_manager.enableAutomaticObjectNamingScheme', $config['enableAutomaticObjectNamingScheme']);
    }

    private function registerCustomerSaveValidatorConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setAlias('cmf.customer_save_validator', CustomerSaveValidatorInterface::class);

        $container->setParameter('pimcore_customer_management_framework.customer_save_validator.requiredFields', is_array($config['requiredFields']) ? $config['requiredFields'] : []);
        $container->setParameter('pimcore_customer_management_framework.customer_save_validator.checkForDuplicates', $config['checkForDuplicates']);
    }

    private function registerSegmentManagerConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setAlias('cmf.segment_manager', SegmentManagerInterface::class);

        $container->setParameter('pimcore_customer_management_framework.segment_manager.segmentFolder.calculated', $config['segmentFolder']['calculated']);
        $container->setParameter('pimcore_customer_management_framework.segment_manager.segmentFolder.manual', $config['segmentFolder']['manual']);
    }

    private function registerCustomerProviderConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setAlias('cmf.customer_provider', CustomerProviderInterface::class);
        $container->setAlias('cmf.customer_provider.object_naming_scheme', ObjectNamingSchemeInterface::class);
        $container->setAlias('cmf.customer_merger', CustomerMergerInterface::class);

        $container->setParameter('pimcore_customer_management_framework.customer_provider.namingScheme', $config['namingScheme']);
        $container->setParameter('pimcore_customer_management_framework.customer_provider.parentPath', $config['parentPath']);
        $container->setParameter('pimcore_customer_management_framework.customer_provider.archiveDir', $config['archiveDir']);
    }


    private function registerCustomerListConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter('pimcore_customer_management_framework.customer_list.exporters', $config['exporters'] ?: []);
        $container->setParameter('pimcore_customer_management_framework.customer_list.filter_properties', $config['filter_properties'] ?: []);

    }


    private function registerCustomerDuplicatesServicesConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setAlias('cmf.customer_duplicates_service', CustomerDuplicatesServiceInterface::class);
        $container->setAlias('cmf.customer_duplicates_index', DuplicatesIndexInterface::class);

        $container->setParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicateCheckFields', $config['duplicateCheckFields']);
        $container->setParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_view.listFields', $config['duplicates_view']['listFields'] ?: []);
        $container->setParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_index.enableDuplicatesIndex', $config['duplicates_index']['enableDuplicatesIndex'] ?: []);
        $container->setParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_index.duplicateCheckFields', $config['duplicates_index']['duplicateCheckFields'] ?: []);
        $container->setParameter('pimcore_customer_management_framework.customer_duplicates_services.duplicates_index.dataTransformers', $config['duplicates_index']['dataTransformers'] ?: []);
    }

    private function registerNewsletterConfiguration(ContainerBuilder $container, array $config)
    {

        $container->setParameter('pimcore_customer_management_framework.newsletter.newsletterSyncEnabled', (bool) $config['newsletterSyncEnabled']);
        $container->setParameter('pimcore_customer_management_framework.newsletter.newsletterSyncEnabled', (bool) $config['newsletterSyncEnabled']);
        $container->setParameter('pimcore_customer_management_framework.newsletter.newsletterQueueImmidiateAsyncExecutionEnabled', (bool) $config['newsletterQueueImmidiateAsyncExecutionEnabled']);

        if($config['newsletterSyncEnabled']) {
            $container->setAlias('cmf.newsletter.queue', NewsletterQueueInterface::class);

            $container->setParameter('pimcore_customer_management_framework.newsletter.mailchimp.apiKey', $config['mailchimp']['apiKey']);
            $container->setParameter('pimcore_customer_management_framework.newsletter.mailchimp.cliUpdatesPimcoreUserName', $config['mailchimp']['cliUpdatesPimcoreUserName']);
        }

    }
}
