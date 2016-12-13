<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 14:06
 */

namespace CustomerManagementFramework;

use Pimcore\Logger;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabRepository;

class Installer {

    public function install() {

       // $this->installPermissions();
       // $this->installDatabaseTables();
       // $this->installClasses();
        $this->installCrontab();

        return true;
    }

    private function installPermissions() {

        $permissions = [
            "plugin_customermanagementframework_activityview",
            "plugin_customermanagementframework_customerview"
        ];

        foreach($permissions as $key) {
            $permission = new \Pimcore\Model\User\Permission\Definition();
            $permission->setKey($key);

            $res = new \Pimcore\Model\User\Permission\Definition\Dao();
            $res->configure(\Pimcore\Db::get());
            $res->setModel($permission);
            $res->save();
        }
    }

    private function installDatabaseTables() {
        \Pimcore\Db::get()->query(
            "CREATE TABLE `plugin_cmf_activities` (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE `plugin_cmf_deletions` (
              `id` int(11) unsigned NOT NULL,
              `entityType` char(20) NOT NULL,
              `type` varchar(255) NOT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              KEY `type` (`entityType`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE `plugin_cmf_segmentbuilder_changes_queue` (
              `customerId` int(11) unsigned NOT NULL,
              UNIQUE KEY `customerId` (`customerId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
    }

    private static function installClasses()
    {
        self::installClass("CustomerSegmentGroup", PIMCORE_PLUGINS_PATH . '/CustomerManagementFramework/install/class_source/class_CustomerSegmentGroup_export.json');
        self::installClass("CustomerSegment", PIMCORE_PLUGINS_PATH . '/CustomerManagementFramework/install/class_source/class_CustomerSegment_export.json');
    }

    private static function installClass($classname, $filepath) {
        $class = \Pimcore\Model\Object\ClassDefinition::getByName($classname);
        if(!$class) {
            $class = new \Pimcore\Model\Object\ClassDefinition();
            $class->setName($classname);
        }
        $json = file_get_contents($filepath);

        $success = \Pimcore\Model\Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json);
        if(!$success){
            Logger::err("Could not import $classname Class.");
        }
    }

    private static function installCrontab() {
        

        $crons = [
            '* * * * * php ' . PIMCORE_DOCUMENT_ROOT . '/pimcore/cli/console.php cmf:handle-cron-triggers -v > ' . PIMCORE_LOG_DIRECTORY . '/cmf-cron-trigger-lastrun.log',
            '*/7 * * * * php ' . PIMCORE_DOCUMENT_ROOT . '/pimcore/cli/console.php cmf:process-actiontrigger-queue -v > ' . PIMCORE_LOG_DIRECTORY . '/cmf-process-actiontrigger-queue-lastrun.log',
            '*/5 * * * * php ' . PIMCORE_DOCUMENT_ROOT . '/pimcore/cli/console.php cmf:build-segments -v > ' . PIMCORE_LOG_DIRECTORY . '/cmf-build-segments-queue-lastrun.log',
        ];

        foreach($crons as $cron) {

            try {

                $crontabJob = CrontabJob::createFromCrontabLine($cron);
                $crontabRepository = new CrontabRepository(new CrontabAdapter());
                $crontabJob->comments = 'installed by CMF plugin';
                $crontabRepository->addJob($crontabJob);
                $crontabRepository->persist();
            } catch(\Exception $e) {
                Logger::error($e->getMessage());
            }
        }


    }
}
