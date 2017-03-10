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
use Pimcore\Model\Version;
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

    /**
     * @var CustomerSaveHandlerInterface[]
     */
    protected $saveHandlers;

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

        $this->applySaveHandlers($customer, 'preAdd', true);
        $this->applyNamingScheme($customer);
    }


    public function preUpdate(CustomerInterface $customer)
    {
        if(!$customer->getIdEncoded()) {
            $customer->setIdEncoded(md5($customer->getId()));
        }

        $this->applySaveHandlers($customer, 'preUpdate', true);
        $this->validateOnSave($customer, false);
        $this->applyNamingScheme($customer);
    }

    public function postUpdate(CustomerInterface $customer)
    {
        $this->applySaveHandlers($customer, 'postUpdate');

        if($this->segmentBuildingHookEnabled) {
            Factory::getInstance()->getSegmentManager()->buildCalculatedSegmentsOnCustomerSave($customer);
        }

        Factory::getInstance()->getSegmentManager()->addCustomerToChangesQueue($customer);
        Factory::getInstance()->getCustomerDuplicatesService()->updateDuplicateIndexForCustomer($customer);
    }

    public function preDelete(CustomerInterface $customer)
    {
        $this->applySaveHandlers($customer, 'preDelete', true);
    }

    public function postDelete(CustomerInterface $customer)
    {
        $this->applySaveHandlers($customer, 'postDelete');
    }

    public function validateOnSave(CustomerInterface $customer, $withDuplicatesCheck = true) {

        if(!$this->customerSaveValidatorEnabled) {
            return false;
        }

        /**
         * @var CustomerSaveValidatorInterface $validator
         */
        $validator = \Pimcore::getDiContainer()->get('CustomerManagementFramework\CustomerSaveValidator');

        $validator->validate($customer, $withDuplicatesCheck);
    }

    protected function applySaveHandlers(CustomerInterface $customer, $saveHandlerMethod, $reinitSaveHandlers = false)
    {
        $saveHandlers = $this->createSaveHandlers();

        if($reinitSaveHandlers) {
            $this->reinitSaveHandlers($saveHandlers, $customer);
        }


        foreach($saveHandlers as $handler) {
            $this->logger->debug(sprintf("apply save handler %s %s method to customer %s", get_class($handler), $saveHandlerMethod, (string)$customer));

            if($saveHandlerMethod == 'preAdd') {
                $handler->preAdd($customer);
                $handler->preSave($customer);

            } elseif($saveHandlerMethod == 'preUpdate') {
                $handler->preUpdate($customer);
                $handler->preSave($customer);

            } elseif($saveHandlerMethod == 'postUpdate') {
                $handler->postUpdate($customer);
                $handler->postSave($customer);

            } elseif($saveHandlerMethod == 'postAdd') {
                $handler->postAdd($customer);
                $handler->postSave($customer);

            } elseif($saveHandlerMethod == 'preDelete') {
                $handler->preDelete($customer);

            } elseif($saveHandlerMethod == 'postDelete') {
                $handler->postDelete($customer);
            }
        }
    }

    /**
     * @param CustomerSaveHandlerInterface[] $saveHandlers
     * @param CustomerInterface $customer
     */
    protected function reinitSaveHandlers(array $saveHandlers, CustomerInterface $customer)
    {
        $originalCustomer = null;
        foreach($saveHandlers as $handler) {
            if($handler->isOriginalCustomerNeeded()) {
                \Pimcore::collectGarbage();
                $originalCustomer = Factory::getInstance()->getCustomerProvider()->getById($customer->getId());
                break;
            }
        }

        foreach($saveHandlers as $handler) {
            if($handler->isOriginalCustomerNeeded()) {
                $handler->setOriginalCustomer($originalCustomer);
            }
        }
    }

    /**
     * @return CustomerSaveHandlerInterface[]
     */
    protected function createSaveHandlers()
    {
        if(is_null($this->saveHandlers)) {
            $saveHandlers = [];
            foreach($this->config->saveHandlers as $saveHandlerConfig) {

                $class = (string)$saveHandlerConfig->saveHandler;

                /**
                 * @var CustomerSaveHandlerInterface $saveHandler
                 */
                $saveHandler = Factory::getInstance()->createObject($class, CustomerSaveHandlerInterface::class, ["config" => $saveHandlerConfig, "logger" => $this->logger]);
                $saveHandlers[] = $saveHandler;
            }

            $this->saveHandlers = $saveHandlers;
        }

        return $this->saveHandlers;
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

    public function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false)
    {
        $customerSaveValidatorEnabled = $this->getCustomerSaveValidatorEnabled();
        $segmentBuildingHookEnabled = $this->getSegmentBuildingHookEnabled();

        $versionsEnabled = !Version::$disabled;
        if($disableVersions) {
            Version::disable();
        }

        $this->setSegmentBuildingHookEnabled(false);
        $this->setCustomerSaveValidatorEnabled(false);

        $result = $customer->save();

        $this->setSegmentBuildingHookEnabled($segmentBuildingHookEnabled);
        $this->setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled);

        if($disableVersions && $versionsEnabled) {
            Version::enable();
        }

        return $result;
    }

    protected function applyNamingScheme(CustomerInterface $customer)
    {
        if($this->config->enableAutomaticObjectNamingScheme) {
            Factory::getInstance()->getCustomerProvider()->applyObjectNamingScheme($customer);
        }
    }

}
