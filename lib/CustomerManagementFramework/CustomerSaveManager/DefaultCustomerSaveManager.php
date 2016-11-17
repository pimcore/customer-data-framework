<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:10
 */

namespace CustomerManagementFramework\CustomerSaveManager;

use CustomerManagementFramework\DataTransformer\CustomerDataTransformer\CustomerDataTransformerInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Psr\Log\LoggerInterface;

class DefaultCustomerSaveManager implements CustomerSaveManagerInterface
{
    private $segmentBuildingHookEnabled = true;

    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {

        $config = Plugin::getConfig();
        $this->config = $config->CustomerSaveManager;
        $this->logger = $logger;
    }

    public function preUpdate(CustomerInterface $customer)
    {
        $this->applyDataTransformers($customer);
    }

    public function postUpdate(CustomerInterface $customer)
    {
        $this->applyDataTransformers($customer);

        if($this->segmentBuildingHookEnabled) {
            Factory::getInstance()->getSegmentManager()->buildCalculatedSegmentsOnCustomerSave($customer);
        }

        Factory::getInstance()->getSegmentManager()->addCustomerToChangesQueue($customer);
    }

    public function applyDataTransformers(CustomerInterface $customer)
    {
        foreach($this->createDataTransformers() as $dataTransformer) {
            $this->logger->info(sprintf("apply data transformer %s to customer %s", get_class($dataTransformer), (string)$customer));
            $dataTransformer->transform($customer);
        }
    }

    /**
     * @return CustomerDataTransformerInterface[]
     */
    protected function createDataTransformers()
    {
        $dataTransformers = [];
        foreach($this->config->dataTransformers as $dataTransformerConfig) {

            $class = (string)$dataTransformerConfig->dataTransformer;

            $dataTransformers[] = Factory::getInstance()->createObject($class, CustomerDataTransformerInterface::class, [$dataTransformerConfig]);
        }

        return $dataTransformers;
    }

    /**
     * @return boolean
     */
    public function getSegmentBuildingHookEnabled()
    {
        return $this->segmentBuildingHookEnabled;
    }

    /**
     * @param boolean $segmentBuildingHookEnabled
     */
    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled)
    {
        $this->segmentBuildingHookEnabled = $segmentBuildingHookEnabled;
    }




}