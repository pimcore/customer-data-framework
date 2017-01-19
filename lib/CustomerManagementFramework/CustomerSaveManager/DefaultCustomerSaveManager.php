<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:10
 */

namespace CustomerManagementFramework\CustomerSaveManager;

use CustomerManagementFramework\CustomerSaveHandler\CustomerSaveHandlerInterface;
use CustomerManagementFramework\CustomerSaveValidator\CustomerSaveValidatorInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Plugin;
use Psr\Log\LoggerInterface;

class DefaultCustomerSaveManager implements CustomerSaveManagerInterface
{
    private $segmentBuildingHookEnabled = true;
    private $customerSaveValidatorEnabled = true;

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
    public function preAdd(CustomerInterface $customer) {
        if($customer->getPublished()) {
            $this->validateOnSave($customer);
        }
    }


    public function preUpdate(CustomerInterface $customer)
    {
        if(!$customer->getIdEncoded()) {
            $customer->setIdEncoded(md5($customer->getId()));
        }

        $this->applySaveHandlers($customer);
        $this->validateOnSave($customer);

        /*$ex = new ValidationException('...');
        $ex->setSubItems(["test"=>"tester"]);*/

       // throw $ex;
    }

    public function postUpdate(CustomerInterface $customer)
    {
      // $this->applyDataTransformers($customer);

        if($this->segmentBuildingHookEnabled) {
            Factory::getInstance()->getSegmentManager()->buildCalculatedSegmentsOnCustomerSave($customer);
        }

        Factory::getInstance()->getSegmentManager()->addCustomerToChangesQueue($customer);
    }

    public function validateOnSave(CustomerInterface $customer) {

        if(!$this->customerSaveValidatorEnabled) {
            return false;
        }

        /**
         * @var CustomerSaveValidatorInterface $validator
         */
        $validator = \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerSaveValidator');

        $validator->validate($customer);
    }

    public function applySaveHandlers(CustomerInterface $customer)
    {
        foreach($this->createSaveHandlers() as $handler) {
            $this->logger->info(sprintf("apply save handler %s to customer %s", get_class($handler), (string)$customer));
            $handler->process($customer);
        }
    }

    /**
     * @return CustomerSaveHandlerInterface[]
     */
    protected function createSaveHandlers()
    {
        $saveHandlers = [];
        foreach($this->config->saveHandlers as $saveHandlerConfig) {

            $class = (string)$saveHandlerConfig->saveHandler;

            $saveHandlers[] = Factory::getInstance()->createObject($class, CustomerSaveHandlerInterface::class, [$saveHandlerConfig, $this->logger]);
        }

        return $saveHandlers;
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

    /**
     * @return bool
     */
    public function getCustomerSaveValidatorEnabled()
    {
        return $this->customerSaveValidatorEnabled;
    }

    /**
     * @param bool $customerSaveValidatorEnabled
     */
    public function setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled)
    {
        $this->customerSaveValidatorEnabled = $customerSaveValidatorEnabled;
    }




}