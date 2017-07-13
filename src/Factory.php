<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFrameworkBundle;

use CustomerManagementFrameworkBundle\ActionTrigger\ActionManager\ActionManagerInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\EventHandler\EventHandlerInterface;
use CustomerManagementFrameworkBundle\ActionTrigger\Queue\QueueInterface;
use CustomerManagementFrameworkBundle\ActivityManager\ActivityManagerInterface;
use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\ActivityUrlTracker\ActivityUrlTrackerInterface;
use CustomerManagementFrameworkBundle\ActivityView\ActivityViewInterface;
use CustomerManagementFrameworkBundle\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFrameworkBundle\CustomerDuplicatesService\CustomerDuplicatesServiceInterface;
use CustomerManagementFrameworkBundle\CustomerMerger\CustomerMergerInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\CustomerDuplicatesView\CustomerDuplicatesViewInterface;
use CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface;
use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use CustomerManagementFrameworkBundle\RESTApi\ActivitiesHandler;
use CustomerManagementFrameworkBundle\RESTApi\CustomersHandler;
use CustomerManagementFrameworkBundle\RESTApi\DeletionsHandler;
use CustomerManagementFrameworkBundle\RESTApi\SegmentGroupsHandler;
use CustomerManagementFrameworkBundle\RESTApi\SegmentsHandler;
use CustomerManagementFrameworkBundle\RESTApi\SegmentsOfCustomerHandler;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

class Factory
{


    private function __construct()
    {

    }

    /**
     * @return static
     */
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    /**
     * @return SsoIdentityServiceInterface
     */
    public function getSsoIdentityService()
    {
        return \Pimcore::getDiContainer()->get(SsoIdentityServiceInterface::class);
    }


    /**
     * @param            $className
     * @param null $needsToBeSubclassOf
     * @param array|null $constructorParams
     *
     * @return mixed
     * @throws \Exception
     */
    public function createObject($className, $needsToBeSubclassOf = null, array $constructorParams = [])
    {
        if (!class_exists($className)) {
            throw new \Exception(sprintf("class %s does not exist", $className));
        }

        $object = new $className(...array_values($constructorParams));

        if (!is_null($needsToBeSubclassOf) && !is_subclass_of($object, $needsToBeSubclassOf)) {
            throw new \Exception(sprintf("%s needs to extend/implement %s", $className, $needsToBeSubclassOf));
        }

        return $object;
    }
}
