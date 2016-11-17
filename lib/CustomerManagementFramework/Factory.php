<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFramework;

use CustomerManagementFramework\ActivityManager\ActivityManagerInterface;
use CustomerManagementFramework\ActivityStore\ActivityStoreInterface;
use CustomerManagementFramework\ActivityView\ActivityViewInterface;
use CustomerManagementFramework\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFramework\RESTApi\ExportInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

class Factory {


    private function __construct()
    {

    }

    /**
     * @return static
     */
    private static $instance;
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * @return ActivityManagerInterface
     */
    public function getActivityManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityManager');
    }


    /**
     * @return ActivityStoreInterface
     */
    public function getActivityStore()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityStore');
    }


    /**
     * @return ActivityViewInterface
     */
    public function getActivityView()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityView');
    }

    /**
     * @return CustomerSaveManagerInterface
     */
    public function getCustomerSaveManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerSaveManager');
    }

    /**
     * @return SegmentManagerInterface
     */
    public function getSegmentManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\SegmentManager');
    }
    
    /**
     * @return ExportInterface
     */
    public function getRESTApiExport() {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\RESTApi\Export');
    }


    /**
     * @param                 $className
     * @param LoggerInterface $logger
     * @param null            $needsToBeSubclassOf
     *
     * @return mixed
     */
    public function createObject($className, $needsToBeSubclassOf = null, $constructorParam = null)
    {
        if(!class_exists($className)) {
            throw new \Exception(sprintf("class %s does not exist", $className));
        }

        if($needsToBeSubclassOf && !is_subclass_of($className, $needsToBeSubclassOf)) {
            throw new \Exception(sprintf("%s needs to extend/implement %s", $className, $needsToBeSubclassOf));
        }

        if(is_null($constructorParam)) {
            return new $className();
        }

        return new $className($constructorParam);
    }
}