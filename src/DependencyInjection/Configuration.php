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
        $rootNode->append($this->buildEncryptionNode());
        $rootNode->append($this->buildCustomerSaveManagerNode());
        $rootNode->append($this->buildCustomerSaveValidatorNode());
        $rootNode->append($this->buildSegmentManagerNode());
        $rootNode->append($this->buildCustomerProviderNode());
        $rootNode->append($this->buildCustomerListNode());
        $rootNode->append($this->buildCustomerDuplicatesServicesNode());

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
                ->scalarNode('mailBlackListFile')
                    ->defaultValue(PIMCORE_CONFIGURATION_DIRECTORY . '/cmf/mail-blacklist.txt')
                ->end()
            ->end()
        ;

        return $general;
    }

    private function buildEncryptionNode()
    {
        $treeBuilder = new TreeBuilder();

        $general = $treeBuilder->root('encryption');

        $general
            ->addDefaultsIfNotSet()
            ->info('Configuration of EncryptionService');

        $general
            ->children()
            ->scalarNode('secret')
                ->info('
                    echo \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
                    keep it secret
                ')
                ->defaultValue('')
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

    private function buildCustomerSaveValidatorNode()
    {
        $treeBuilder = new TreeBuilder();

        $customerSaveValidator = $treeBuilder->root('customer_save_validator');

        $customerSaveValidator
            ->addDefaultsIfNotSet()
            ->info('Configuration of customer save manager');

        $customerSaveValidator
            ->children()
                ->booleanNode('checkForDuplicates')
                ->info('If enabled an exception will be thrown when saving a customer object if duplicate customers exist. Caution: this is only applied to new customer instances and not when a customer get\'s updated.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('requiredFields')
                        ->prototype('array')
                            ->prototype('scalar')
                ->end()
        ;

        return $customerSaveValidator;
    }

    private function buildSegmentManagerNode()
    {
        $treeBuilder = new TreeBuilder();

        $segmentManager = $treeBuilder->root('segment_manager');

        $segmentManager
            ->addDefaultsIfNotSet()
            ->info('Configuration of segment manager');

        $segmentManager
            ->children()
                ->arrayNode('segmentFolder')
                    ->children()
                        ->scalarNode('manual')
                            ->defaultValue('/segments/manual')
                        ->end()
                        ->scalarNode('calculated')
                            ->defaultValue('/segments/calculated')
                        ->end()
                ->end()
        ;

        return $segmentManager;
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
                ->scalarNode('archiveDir')
                    ->defaultValue('/customers/_archive')
                ->end()
                ->scalarNode('namingScheme')
                    ->defaultNull()
                ->end()
        ;

        return $customerProvider;
    }

    private function buildCustomerListNode()
    {
        $treeBuilder = new TreeBuilder();

        $customerList = $treeBuilder->root('customer_list');

        $customerList
            ->addDefaultsIfNotSet()
            ->info('Configuration of customer list view');

        $defaultExporters = [
            'csv' => [
                'name'       => 'CSV',
                'icon'       => 'fa fa-file-text-o',
                'exporter'   => \CustomerManagementFrameworkBundle\CustomerList\Exporter\Csv::class,
                'properties' => [
                    'id',
                    'active',
                    'gender',
                    'email',
                    'phone',
                    'firstname',
                    'lastname',
                    'street',
                    'zip',
                    'city',
                    'countryCode',
                    'idEncoded',
                ],
                'exportSegmentsAsColumns' => true
            ],

            'xlsx' => [
                'name'       => 'XLSX',
                'icon'       => 'fa fa-file-excel-o',
                'exporter'   => \CustomerManagementFrameworkBundle\CustomerList\Exporter\Xlsx::class,
                'properties' => [
                    'id',
                    'active',
                    'gender',
                    'email',
                    'phone',
                    'firstname',
                    'lastname',
                    'street',
                    'zip',
                    'city',
                    'countryCode',
                    'idEncoded',
                ],
                'exportSegmentsAsColumns' => true
            ],
        ];

        $customerList
            ->children()
            ->arrayNode('exporters')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->isRequired()->end()
                        ->scalarNode('icon')->isRequired()->end()
                        ->scalarNode('exporter')->isRequired()->end()
                        ->booleanNode('exportSegmentsAsColumns')->defaultFalse()->end()
                        ->arrayNode('properties')->isRequired()->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
            ->defaultValue($defaultExporters)
        ;

        return $customerList;
    }

    private function buildCustomerDuplicatesServicesNode()
    {
        $treeBuilder = new TreeBuilder();

        $customerList = $treeBuilder->root('customer_duplicates_services');

        $customerList
            ->addDefaultsIfNotSet()
            ->info('Configuration of customer duplicates services');

        $defaultListFields = [
            ['id'],
            ['email'],
            ['firstname', 'lastname'],
            ['street'],
            ['zip', 'city']
        ];

        $customerList
            ->children()
                ->arrayNode('duplicates_view')
                    ->children()
                        ->arrayNode('listFields')
                            ->prototype('array')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->defaultValue($defaultListFields)
                        ->end()
                    ->end()
                ->end()
        ;

        return $customerList;
    }
}
