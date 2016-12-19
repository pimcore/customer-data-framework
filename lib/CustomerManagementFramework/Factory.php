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
use CustomerManagementFramework\ActionTrigger\TriggerDefinition;
use CustomerManagementFramework\ActionTrigger\Trigger\TriggerDefinitionInterface;
use CustomerManagementFramework\ActivityManager\ActivityManagerInterface;
use CustomerManagementFramework\ActivityStore\ActivityStoreInterface;
use CustomerManagementFramework\ActivityView\ActivityViewInterface;
use CustomerManagementFramework\Authentication\SsoIdentity\SsoIdentityServiceInterface;
use CustomerManagementFramework\CustomerDuplicatesService\CustomerDuplicatesServiceInterface;
use CustomerManagementFramework\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFramework\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFramework\CustomerView\CustomerViewInterface;
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
     * @return CustomerProviderInterface
     */
    public function getCustomerProvider()
    {
        return \Pimcore::getDiContainer()->get(CustomerProviderInterface::class);
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
     * @return ExportInterface
     */
    public function getRESTApiExport() {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\RESTApi\Export');
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
     * @return Mailchimp\ExportService
     */
    public function getMailchimpExportService()
    {
        return \Pimcore::getDiContainer()->get('CustomerManagementFramework\Mailchimp\ExportService');
    }

    /**
     * @param                 $className
     * @param LoggerInterface $logger
     * @param null            $needsToBeSubclassOf
     *
     * @return mixed
     */
    public function createObject($className, $needsToBeSubclassOf = null, array $constructorParams = null)
    {
        
        if(!\Pimcore::getDiContainer()->has($className)) {
            $definition = \DI\object($className);
            if($constructorParams) {
                foreach($constructorParams as $key => $param) {
                    $definition->constructorParameter($key, $param);
                }
            }

            \Pimcore::getDiContainer()->set($className, $definition);
        }

        $object = \Pimcore::getDiContainer()->make($className);

        if(!is_subclass_of($object, $needsToBeSubclassOf)) {
            throw new \Exception(sprintf("%s needs to extend/implement %s", $className, $needsToBeSubclassOf));
        }

        return $object;
    }
}
