<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle;

use CustomerManagementFrameworkBundle\Migrations\PimcoreX\Version20210305134111;
use Pimcore\Db;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Logger;

class Installer extends SettingsStoreAwareInstaller
{
    private $permissionsToInstall = [
        'plugin_cmf_perm_activityview',
        'plugin_cmf_perm_customerview',
        'plugin_cmf_perm_customerview_admin',
        'plugin_cmf_perm_customer_automation_rules',
        'plugin_cmf_perm_newsletter_enqueue_all_customers',
    ];

    public function install(): void
    {
        $this->installPermissions();
        $this->installDatabaseTables();
        $this->installClasses();
        $this->installDependentBundles();

        parent::install();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function needsReloadAfterInstall(): bool
    {
        return true;
    }

    public function installPermissions()
    {
        foreach ($this->permissionsToInstall as $key) {
            $permission = new \Pimcore\Model\User\Permission\Definition();
            $permission->setKey($key);

            $res = new \Pimcore\Model\User\Permission\Definition\Dao();
            $res->configure();
            $res->setModel($permission);
            $res->save();
        }
    }

    public function installDatabaseTables()
    {
        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_activities` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `customerId` int(11) unsigned NOT NULL,
              `activityDate` bigint(20) unsigned DEFAULT NULL,
              `type` varchar(255) NOT NULL,
              `implementationClass` varchar(255) NOT NULL,
              `o_id` int(11) unsigned DEFAULT NULL,
              `a_id` varchar(255) DEFAULT NULL,
              `attributes` blob,
              `md5` char(32) DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              `modificationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `customerId` (`customerId`),
              KEY `o_id` (`o_id`),
              KEY `a_id` (`a_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_deletions` (
              `id` int(11) unsigned NOT NULL,
              `entityType` char(20) NOT NULL,
              `type` varchar(255) NOT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`, `entityType`, `type`),
              KEY `type` (`entityType`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_segmentbuilder_changes_queue` (
              `customerId` int(11) unsigned NOT NULL,
              UNIQUE KEY `customerId` (`customerId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_actiontrigger_actions` (
              `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
              `ruleId` int(20) unsigned NOT NULL,
              `actionDelay` int(20) unsigned NOT NULL,
              `implementationClass` varchar(255) NOT NULL,
              `options` text,
              `creationDate` bigint(20) NOT NULL,
              `modificationDate` bigint(20) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `ruleId` (`ruleId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_actiontrigger_rules` (
              `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) DEFAULT NULL,
              `description` text,
              `active` tinyint(1) unsigned DEFAULT NULL,
              `trigger` text COMMENT 'configuration of triggers',
              `condition` text COMMENT 'configuration of conditions',
              `creationDate` int(11) NOT NULL,
              `modificationDate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`),
              KEY `active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_actiontrigger_queue` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `customerId` int(11) unsigned NOT NULL,
              `actionDate` bigint(20) unsigned DEFAULT NULL,
              `actionId` int(11) unsigned DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              `modificationDate` bigint(20) unsigned DEFAULT NULL,
              `environment` TEXT NULL,
              PRIMARY KEY (`id`),
              KEY `customerId` (`customerId`),
              KEY `actionId` (`actionId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_sequence_numbers` (
              `name` char(50) NOT NULL,
              `number` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        Db::get()->executeQuery(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_duplicatesindex` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `duplicateData` text NOT NULL,
              `duplicateDataMd5` varchar(32) DEFAULT NULL,
              `fieldCombination` char(255) NOT NULL DEFAULT '',
              `fieldCombinationCrc` int(11) unsigned NOT NULL,
              `metaphone` varchar(50) DEFAULT NULL,
              `soundex` varchar(50) DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `duplicateDataCrc` (`duplicateDataMd5`),
              KEY `fieldCombination` (`fieldCombination`),
              KEY `soundex` (`soundex`),
              KEY `metaphone` (`metaphone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_duplicatesindex_customers` (
              `duplicate_id` int(11) unsigned NOT NULL,
              `customer_id` int(11) unsigned NOT NULL,
              KEY `duplicate_id` (`duplicate_id`),
              KEY `customer_id` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_duplicates_false_positives` (
              `row1` text NOT NULL,
              `row2` text NOT NULL,
              `row1Details` text NOT NULL,
              `row2Details` text NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        Db::get()->executeQuery(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_potential_duplicates` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `duplicateCustomerIds` varchar(255) NOT NULL DEFAULT '',
              `fieldCombinations` text NOT NULL,
              `declined` tinyint(1) DEFAULT NULL,
              `modificationDate` bigint(20) unsigned DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `duplicateIds` (`duplicateCustomerIds`),
              KEY `declined` (`declined`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        Db::get()->executeQuery(
            'CREATE TABLE IF NOT EXISTS `plugin_cmf_newsletter_queue` (
              `customerId` int(11) unsigned NOT NULL,
              `email` varchar(255) DEFAULT NULL,
              `operation` varchar(20) DEFAULT NULL,
              `modificationDate` bigint(20) DEFAULT NULL,
              UNIQUE KEY `customerId` (`customerId`),
              KEY `operation` (`operation`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $sqlFiles = [
            __DIR__ . '/Resources/sql/filterDefinition/' => [
                'datamodel.sql'
            ],
            __DIR__ . '/Resources/sql/activityMetadata/' => [
                'datamodel.sql'
            ]
        ];

        $storedFunctions = [
            __DIR__ . '/Resources/sql/segmentAssignment/' => [
                'datamodel.sql',
                'storedFunctionDocument.sql',
                'storedFunctionAsset.sql',
            ],
        ];

        $sqlFiles[__DIR__ . '/Resources/sql/segmentAssignment/'][] = 'storedFunctionObject.sql';

        $db = Db::get();

        foreach ($sqlFiles as $folder => $files) {
            foreach ($files as $file) {
                $statement = file_get_contents($folder.$file);
                $db->executeQuery($statement);
            }
        }

        foreach ($storedFunctions as $folder => $files) {
            foreach ($files as $file) {
                $statement = file_get_contents($folder.$file);
                $db->executeStatement($statement);
            }
        }
    }

    public function installClasses()
    {
        $sourcePath = __DIR__.'/../install/class_source';

        self::installClass('CustomerSegmentGroup', $sourcePath.'/class_CustomerSegmentGroup_export.json');
        self::installClass('CustomerSegment', $sourcePath.'/class_CustomerSegment_export.json');
        self::installClass(
            'TermSegmentBuilderDefinition',
            $sourcePath.'/class_TermSegmentBuilderDefinition_export.json'
        );

        self::installClass('LinkActivityDefinition', $sourcePath.'/class_LinkActivityDefinition_export.json');
    }

    public static function installClass($classname, $filepath)
    {
        $class = \Pimcore\Model\DataObject\ClassDefinition::getByName($classname);
        if (!$class) {
            $class = new \Pimcore\Model\DataObject\ClassDefinition();
            $class->setName($classname);
            $class->setGroup('CustomerManagement');

            $json = file_get_contents($filepath);

            $success = \Pimcore\Model\DataObject\ClassDefinition\Service::importClassDefinitionFromJson($class, $json);
            if (!$success) {
                Logger::err("Could not import $classname Class.");
            }
        }
    }

    public static function installBrick($brickKey, $filepath)
    {
        try {
            $brick = \Pimcore\Model\DataObject\Objectbrick\Definition::getByKey($brickKey);
        } catch (\Exception $e) {
            $brick = null;
        }

        if (!$brick) {
            $brick = new \Pimcore\Model\DataObject\Objectbrick\Definition;
            $brick->setKey($brickKey);

            $json = file_get_contents($filepath);

            $success = \Pimcore\Model\DataObject\ClassDefinition\Service::importObjectBrickFromJson($brick, $json);
            if (!$success) {
                Logger::err("Could not import $brickKey brick.");
            }
        }
    }

    public function installDependentBundles(): void
    {
        $appLoggerInstaller = \Pimcore::getContainer()->get(\Pimcore\Bundle\ApplicationLoggerBundle\Installer::class);

        if (!$appLoggerInstaller->isInstalled()) {
            $appLoggerInstaller->install();
        }

        $personalizationInstaller = \Pimcore::getContainer()->get(\Pimcore\Bundle\PersonalizationBundle\Installer::class);

        if (!$personalizationInstaller->isInstalled()) {
            $personalizationInstaller->install();
        }
    }

    public function getLastMigrationVersionClassName(): ?string
    {
        return Version20210305134111::class;
    }
}
