<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\DependencyInjection;

use Pimcore\Model\DataObject\AbstractObject;
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
        $rootNode->append($this->buildCustomerProviderNode());
        $rootNode->append($this->buildCustomerSaveValidatorNode());
        $rootNode->append($this->buildSegmentManagerNode());
        $rootNode->append($this->buildCustomerListNode());
        $rootNode->append($this->buildCustomerDuplicatesServicesNode());
        $rootNode->append($this->buildNewsletterNode());
        $rootNode->append($this->buildActivityUrlTrackerNode());
        $rootNode->append($this->buildSegmentAssignmentClassPermission());
        $rootNode->append($this->buildGDPRConfigNode());

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

        $general
            ->children()
            ->scalarNode('customerPimcoreClass')
            ->defaultValue('Customer')
            ->end()
            ->scalarNode('mailBlackListFile')
            ->defaultValue(PIMCORE_CONFIGURATION_DIRECTORY . '/cmf/mail-blacklist.txt')
            ->end()
            ->end();

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
                ->info(
                    'echo \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();' . PHP_EOL .
                    'keep it secret'
                )
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
                    ->info('If enabled the automatic object naming scheme will be applied on each customer save. See: customer_provider -> namingScheme option')
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
                ->info('If enabled an exception will be thrown when saving a customer object if duplicate customers exist.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('requiredFields')
                        ->prototype('array')
                            ->prototype('scalar')->end()
                        ->info('Provide valid field combinations. The customer object then is valid as soon as at least one of these field combinations is filled up.')
                        ->example([
                            ['email'],
                            ['firstname', 'lastname', 'zip', 'city'],
                        ])
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
                ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('manual')
                            ->defaultValue('/segments/manual')
                            ->info('parent folder of manual segments + segment groups')
                        ->end()
                        ->scalarNode('calculated')
                            ->defaultValue('/segments/calculated')
                            ->info('parent folder of calculated segments + segment groups')
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
                    ->info('parent folder for active customers')
                ->end()
                ->scalarNode('archiveDir')
                    ->defaultValue('/customers/_archive')
                    ->info('parent folder for customers which are unpublished and inactive')
                ->end()
                ->scalarNode('namingScheme')
                    ->defaultNull()
                    ->example('{countryCode}/{zip}/{firstname}-{lastname}')
                    ->info('If a naming scheme is configured customer objects will be automatically renamend and moved to the configured folder structure as soon as the naming scheme gets applied.')
                ->end()
                ->scalarNode('newCustomersTempDir')
                    ->defaultValue('/customers/_temp')
                    ->info('parent folder for customers which are created via the "new customer" button in the customer list view')
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
                'name' => 'CSV',
                'icon' => 'fa fa-file-text-o',
                'exporter' => \CustomerManagementFrameworkBundle\CustomerList\Exporter\Csv::class,
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
                'name' => 'XLSX',
                'icon' => 'fa fa-file-excel-o',
                'exporter' => \CustomerManagementFrameworkBundle\CustomerList\Exporter\Xlsx::class,
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

        $defaultFilterPropertiesEquals = [
            'id' => 'o_id',
            'active' => 'active',
        ];

        $defaultFilterPropertiesSearch = [
            'email' => [
                'email'
            ],
            'name' => [
                'firstname',
                'lastname'
            ],
            'search' => [
                'o_id',
                'idEncoded',
                'firstname',
                'lastname',
                'email',
                'zip',
                'city'
            ]
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
                ->end()

                ->arrayNode('filter_properties')
                    ->addDefaultsIfNotSet()

                    ->children()
                        ->arrayNode('equals')
                            ->defaultValue($defaultFilterPropertiesEquals)

                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('search')
                            ->defaultValue($defaultFilterPropertiesSearch)

                            ->prototype('array')
                                ->prototype('scalar')
                    ->end()
                ->end()
                ->end()
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
                ->arrayNode('duplicateCheckFields')
                    ->defaultValue([])

                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()

                ->arrayNode('duplicateCheckTrimmedFields')
                    ->info('Performance improvement: add duplicate check fields which are trimmed (trim() called on the field value) by a customer save handler. No trim operation will be needed in the resulting query.')
                    ->prototype('scalar')->end()
                ->end()

                ->arrayNode('duplicates_view')
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->arrayNode('listFields')
                            ->prototype('array')
                                ->prototype('scalar')
                                ->end()
                            ->end()
                            ->defaultValue($defaultListFields)
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('duplicates_index')
                    ->children()
                        ->booleanNode('enableDuplicatesIndex')
                            ->defaultFalse()
                        ->end()

                        ->arrayNode('duplicateCheckFields')

                            ->prototype('array')
                                ->prototype('array')
                                    ->children()
                                        ->booleanNode('soundex')->defaultFalse()->end()
                                        ->booleanNode('metaphone')->defaultFalse()->end()
                                        ->scalarNode('similarity')->defaultValue('\CustomerManagementFrameworkBundle\DataSimilarityMatcher\SimilarText')->end()
                                        ->scalarNode('similarityThreshold')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('dataTransformers')
                            ->prototype('scalar')->end()
                        ->end()

                    ->end()
                ->end()

            ->end()
        ;

        return $customerList;
    }

    private function buildNewsletterNode()
    {
        $treeBuilder = new TreeBuilder();

        $newsletter = $treeBuilder->root('newsletter');

        $newsletter
            ->addDefaultsIfNotSet()
            ->info('Configuration of newsletter services');

        $newsletter
            ->children()
                ->booleanNode('newsletterSyncEnabled')->defaultFalse()->end()
                ->booleanNode('newsletterQueueImmediateAsyncExecutionEnabled')->defaultTrue()->end()
                ->arrayNode('mailchimp')
                    ->children()
                        ->scalarNode('apiKey')->end()
                        ->scalarNode('cliUpdatesPimcoreUserName')->end()
                    ->end()
                ->end()
            ->end();

        return $newsletter;
    }

    private function buildActivityUrlTrackerNode()
    {
        $treeBuilder = new TreeBuilder();

        $tracker = $treeBuilder->root('activity_url_tracker');

        $tracker
            ->addDefaultsIfNotSet()
            ->info('Configuration of activity url tracker services');

        $tracker
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->scalarNode('linkCmfcPlaceholder')->defaultValue('*|ID_ENCODED|*')->info('used for automatic link generation of LinkActivityDefinition data objects')->end()
            ->end();

        return $tracker;
    }

    private function buildSegmentAssignmentClassPermission()
    {
        $treeBuilder = new TreeBuilder();

        $assignment = $treeBuilder->root('segment_assignment_classes');

        $assignment
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('document')->info('expects sub types of document')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('folder')->defaultFalse()->end()
                                ->scalarNode('page')->defaultFalse()->end()
                                ->scalarNode('snippet')->defaultFalse()->end()
                                ->scalarNode('link')->defaultFalse()->end()
                                ->scalarNode('hardlink')->defaultFalse()->end()
                                ->scalarNode('email')->defaultFalse()->end()
                                ->scalarNode('newsletter')->defaultFalse()->end()
                                ->scalarNode('printpage')->defaultFalse()->end()
                                ->scalarNode('printcontainer')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('asset')->info('expects sub types of asset')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('folder')->defaultFalse()->end()
                                ->scalarNode('image')->defaultFalse()->end()
                                ->scalarNode('text')->defaultFalse()->end()
                                ->scalarNode('audio')->defaultFalse()->end()
                                ->scalarNode('video')->defaultFalse()->end()
                                ->scalarNode('document')->defaultFalse()->end()
                                ->scalarNode('archive')->defaultFalse()->end()
                                ->scalarNode('unknown')->defaultFalse()->end()
                            ->end()
                        ->end()
                        ->arrayNode('object')->info('expects sub types of object')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode(AbstractObject::OBJECT_TYPE_FOLDER)->defaultFalse()->end()
                                ->arrayNode(AbstractObject::OBJECT_TYPE_OBJECT)
                                    ->prototype('boolean')->end()
                                ->end()
                                ->arrayNode(AbstractObject::OBJECT_TYPE_VARIANT)
                                    ->prototype('boolean')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $assignment;
    }

    private function buildGDPRConfigNode() {

        $treeBuilder = new TreeBuilder();

        $dataObjects = $treeBuilder->root('gdprDataProvider');
        $dataObjects
            ->addDefaultsIfNotSet()
            ->info('Settings for GDPR DataProvider for customers');

        $dataObjects
            ->children()
                ->arrayNode('customer')
                    ->addDefaultsIfNotSet()
                    ->info('Configure which classes should be considered, array key is class name')
                    ->children()
                        ->booleanNode("allowDelete")
                            ->info("Allow delete of objects directly in preview grid.")
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('includedRelations')
                            ->info('List relation attributes that should be included recursively into export.')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $dataObjects;

    }
}
