<?php

namespace CustomerManagementFramework;

use CustomerManagementFramework\Model\ActivityInterface;
use Pimcore\API\Plugin as PluginLib;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface
{

    public function init()
    {
        parent::init();

        $config = self::getConfig();

        \Pimcore::getDiContainer()->set('CustomerManagementFramework\ActivityManager', \DI\object((string)$config->di->ActivityManager ? : 'CustomerManagementFramework\ActivityManager\DefaultActivityManager'));
        \Pimcore::getDiContainer()->set('CustomerManagementFramework\ActivityStore', \DI\object((string)$config->di->ActivityStore ? : 'CustomerManagementFramework\ActivityStore\MariaDb'));
        \Pimcore::getDiContainer()->set('CustomerManagementFramework\ActivityView', \DI\object((string)$config->di->ActivityView ? : 'CustomerManagementFramework\ActivityView\DefaultActivityView'));
        \Pimcore::getDiContainer()->set('CustomerManagementFramework\SegmentManager', \DI\object((string)$config->di->SegmentManager ? : 'CustomerManagementFramework\ActivityManager\DefaultSegmentManager'));
        \Pimcore::getDiContainer()->set('CustomerManagementFramework\RESTApi\Export', \DI\object((string)$config->di->RESTApi->Export ? : 'CustomerManagementFramework\RESTApi\Export'));


        \Pimcore::getEventManager()->attach(["object.postAdd","object.postUpdate"], function (\Zend_EventManager_Event $e) {
            $object = $e->getTarget();

            if($object instanceof ActivityInterface) {
                Factory::getInstance()->getActivityManager()->trackActivity($object);
            }
        });

        \Pimcore::getEventManager()->attach("object.postDelete", function (\Zend_EventManager_Event $e) {
            $object = $e->getTarget();
            if($object instanceof ActivityInterface) {
                Factory::getInstance()->getActivityManager()->deleteActivity($object);
            }
        });

    }

    public function handleDocument($event)
    {
        // do something
        $document = $event->getTarget();
    }

    public static function install()
    {
        $installer = new Installer();

        return $installer->install();
    }
    
    public static function uninstall()
    {
        // implement your own logic here
        return true;
    }

    public static function isInstalled()
    {
        // implement your own logic here
        return true;
    }

    private static function getConfigFile() {
        return \Pimcore\Config::locateConfigFile("plugins/CustomerManagementFramework/config.php");
    }

    protected static $config = null;
    public static function getConfig() {
        if(is_null(self::$config)) {
            $file = self::getConfigFile();

            if(file_exists($file)) {
                self::$config = new \Zend_Config(include($file));;

            } else {
                throw new \Exception($file . " doesn't exist");
            }
        }


        return self::$config;
    }
}
