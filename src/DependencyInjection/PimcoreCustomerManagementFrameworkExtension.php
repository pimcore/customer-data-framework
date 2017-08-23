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

use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
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

        $this->registerCustomerSaveManagerConfiguration($container, $config['customer_save_manager']);
        $this->registerCustomerProviderConfiguration($container, $config['customer_provider'], $config);
    }

    private function registerCustomerSaveManagerConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setAlias('cmf.customer_save_manager', CustomerSaveManagerInterface::class);
        $definition = $container->getDefinition(CustomerSaveManagerInterface::class);
        $definition->addArgument($config['enableAutomaticObjectNamingScheme']);
    }

    private function registerCustomerProviderConfiguration(ContainerBuilder $container, array $config, array $totalConfig)
    {
        $container->setAlias('cmf.customer_provider', CustomerProviderInterface::class);
        $definition = $container->getDefinition(CustomerProviderInterface::class);
        $definition->addArgument($totalConfig['general']['customerPimcoreClass']);
        $definition->addArgument($config['parentPath']);
        $definition->addArgument($config['namingScheme']);
    }
}
