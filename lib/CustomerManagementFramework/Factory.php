<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFramework;

use CustomerManagementFramework\ActionTrigger\ActionManager\ActionManagerInterface;
use CustomerManagementFramework\ActionTrigger\EventHandler\EventHandlerInterface;
use CustomerManagementFramework\ActionTrigger\Queue\QueueInterface;
use CustomerManagementFramework\ActivityManager\ActivityManagerInterface;
use CustomerManagementFramework\ActivityStore\ActivityStoreInterface;
use CustomerManagementFramework\ActivityUrlTracker\ActivityUrlTrackerInterface;
use CustomerManagementFramework\ActivityView\ActivityViewInterface;
use CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFramework\CustomerDuplicatesService\CustomerDuplicatesServiceInterface;
use CustomerManagementFramework\CustomerMerger\CustomerMergerInterface;
use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFramework\CustomerView\CustomerViewInterface;
use CustomerManagementFramework\DuplicatesIndex\DuplicatesIndexInterface;
use CustomerManagementFramework\RESTApi\ActivitiesHandler;
use CustomerManagementFramework\RESTApi\CustomersHandler;
use CustomerManagementFramework\RESTApi\DeletionsHandler;
use CustomerManagementFramework\RESTApi\ExportInterface;
use CustomerManagementFramework\RESTApi\SegmentGroupsHandler;
use CustomerManagementFramework\RESTApi\SegmentsHandler;
use CustomerManagementFramework\RESTApi\SegmentsOfCustomerHandler;
use CustomerManagementFramework\RESTApi\UpdateInterface;
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
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\Logger');
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
     * @return CustomerProviderInterface
     */
    public function getCustomerProvider()
    {
        return \Pimcore::getDiContainer()->get(CustomerProviderInterface::class);
    }

    /**
     * @return CustomerMergerInterface
     */
    public function getCustomerMerger()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerMerger');
    }

    /**
     * @return CustomerViewInterface
     */
    public function getCustomerView()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerView');
    }

    /**
     * @return CustomerDuplicatesServiceInterface
     */
    public function getCustomerDuplicatesService()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerDuplicatesService');
    }

    /**
     * @return DuplicatesIndexInterface
     */
    public function getDuplicatesIndex()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\DuplicatesIndex');
    }


    /**
     * @return CustomerSaveManagerInterface
     */
    public function getCustomerSaveManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerSaveManager');
    }

    /**
     * @return SsoIdentityServiceInterface
     */
    public function getSsoIdentityService()
    {
        return \Pimcore::getDiContainer()->get(SsoIdentityServiceInterface::class);
    }

    /**
     * @return SegmentManagerInterface
     */
    public function getSegmentManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\SegmentManager');
    }


    /**
     * @return CustomersHandler
     */
    public function getRESTApiCustomersHandler()
    {
        return \Pimcore::getDiContainer()->get(CustomersHandler::class);
    }

    /**
     * @return ActivitiesHandler
     */
    public function getRESTApiActivitiesHandler()
    {
        return \Pimcore::getDiContainer()->get(ActivitiesHandler::class);
    }

    /**
     * @return SegmentsOfCustomerHandler
     */
    public function getRESTApiSegmentsOfCustomerHandler()
    {
        return \Pimcore::getDiContainer()->get(SegmentsOfCustomerHandler::class);
    }

    /**
     * @return DeletionsHandler
     */
    public function getRESTApiDeletionsHandler()
    {
        return \Pimcore::getDiContainer()->get(DeletionsHandler::class);
    }

    /**
     * @return SegmentsHandler
     */
    public function getRESTApiSegmentsHandler()
    {
        return \Pimcore::getDiContainer()->get(SegmentsHandler::class);
    }

    /**
     * @return SegmentGroupsHandler
     */
    public function getRESTApiSegmentGroupsHandler()
    {
        return \Pimcore::getDiContainer()->get(SegmentGroupsHandler::class);
    }

    /**
     * @return CustomerList\ExporterManagerInterface
     */
    public function getCustomerListExporterManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerList\ExporterManager');
    }

    /**
     * @return EventHandlerInterface
     */
    public function getActionTriggerEventHandler()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActionTrigger\EventHandler');
    }

    /**
     * @return QueueInterface
     */
    public function getActionTriggerQueue()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActionTrigger\Queue');
    }

    /**
     * @return ActionManagerInterface
     */
    public function getActionTriggerActionManager()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActionTrigger\ActionManager');
    }

    /**
     * @return ActivityUrlTrackerInterface
     */
    public function getActivityUrlTracker()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityUrlTracker');
    }

    /**
     * @param            $className
     * @param null       $needsToBeSubclassOf
     * @param array|null $constructorParams
     *
     * @return mixed
     * @throws \Exception
     */
    public function createObject($className, $needsToBeSubclassOf = null, array $constructorParams = [])
    {
        $object = \Pimcore::getDiContainer()->make($className, $constructorParams);

        if(!is_null($needsToBeSubclassOf) && !is_subclass_of($object, $needsToBeSubclassOf)) {
            throw new \Exception(sprintf("%s needs to extend/implement %s", $className, $needsToBeSubclassOf));
        }

        return $object;
    }
}
