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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('pimcore_customer_management_framework');
        $rootNode->addDefaultsIfNotSet();

        $rootNode
            ->children()
                ->arrayNode('oauth_client')
                    ->canBeEnabled()
                ->end()
            ->end();

        $rootNode->append($this->buildGeneralNode());
        $rootNode->append($this->buildCustomerSaveManagerNode());
        $rootNode->append($this->buildCustomerProviderNode());

        return $treeBuilder;
    }

    private function buildGeneralNode()
    {
        $treeBuilder = new TreeBuilder();

        $general = $treeBuilder->root('general');

        $general
            ->addDefaultsIfNotSet()
            ->info('Configuration of general settings');

        $general
            ->children()
                ->scalarNode('customerPimcoreClass')
                    ->defaultValue('Customer')
                ->end()
        ;

        return $general;
    }

    private function buildCustomerSaveManagerNode()
    {
        $treeBuilder = new TreeBuilder();

        $customerSaveManager = $treeBuilder->root('customer_save_manager');

        $customerSaveManager
            ->addDefaultsIfNotSet()
            ->info('Configuration of customer save manager');

        $customerSaveManager
            ->children()
                ->booleanNode('enableAutomaticObjectNamingScheme')
                    ->defaultFalse()
                ->end()
        ;

        return $customerSaveManager;
    }

    private function buildCustomerProviderNode()
    {
        $treeBuilder = new TreeBuilder();

        $customerProvider = $treeBuilder->root('customer_provider');

        $customerProvider
            ->addDefaultsIfNotSet()
            ->info('Configuration of customer provider');

        $customerProvider
            ->children()
                ->scalarNode('parentPath')
                    ->defaultValue('/customers')
                ->end()
                ->scalarNode('namingScheme')
                    ->defaultNull()
                ->end()
        ;

        return $customerProvider;
    }
}
