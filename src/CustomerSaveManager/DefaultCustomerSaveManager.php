<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:10
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\CustomerSaveHandler\CustomerSaveHandlerInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\CustomerSaveValidatorInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Db;
use Pimcore\Model\Version;

class DefaultCustomerSaveManager implements CustomerSaveManagerInterface
{
    use LoggerAware;

    private $segmentBuildingHookEnabled = true;
    private $customerSaveValidatorEnabled = true;

    private $disableSaveHandlers = false;
    private $disableDuplicateIndex = false;
    private $disableQueue = false;

    protected $config;

    /**
     * @var CustomerSaveHandlerInterface[]
     */
    protected $saveHandlers;

    public function __construct()
    {

        $config = Config::getConfig();
        $this->config = $config->CustomerSaveManager;
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
     * @return $this
     */
    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled)
    {
        $this->segmentBuildingHookEnabled = $segmentBuildingHookEnabled;

        return $this;
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
     * @return $this
     */
    public function setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled)
    {
        $this->customerSaveValidatorEnabled = $customerSaveValidatorEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableSaveHandlers()
    {
        return $this->disableSaveHandlers;
    }

    /**
     * @param bool $disableSaveHandlers
     * @return $this
     */
    public function setDisableSaveHandlers($disableSaveHandlers)
    {
        $this->disableSaveHandlers = $disableSaveHandlers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableDuplicateIndex()
    {
        return $this->disableDuplicateIndex;
    }

    /**
     * @param bool $disableDuplicateIndex
     * @return $this
     */
    public function setDisableDuplicateIndex($disableDuplicateIndex)
    {
        $this->disableDuplicateIndex = $disableDuplicateIndex;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableQueue()
    {
        return $this->disableQueue;
    }

    /**
     * @param bool $disableQueue
     * @return $this
     */
    public function setDisableQueue($disableQueue)
    {
        $this->disableQueue = $disableQueue;

        return $this;
    }


    protected function applyNamingScheme(CustomerInterface $customer)
    {
        if ($this->config->enableAutomaticObjectNamingScheme) {
            \Pimcore::getContainer()->get('cmf.customer_provider')->applyObjectNamingScheme($customer);
        }
    }


    public function preAdd(CustomerInterface $customer)
    {
        if ($customer->getPublished()) {
            $this->validateOnSave($customer);
        }
        if (!$this->isDisableSaveHandlers()) {
            $this->applySaveHandlers($customer, 'preAdd', true);
        }

        $this->applyNamingScheme($customer);
    }


    public function preUpdate(CustomerInterface $customer)
    {
        if (!$customer->getIdEncoded()) {
            $customer->setIdEncoded(md5($customer->getId()));
        }

        if (!$this->isDisableSaveHandlers()) {
            $this->applySaveHandlers($customer, 'preUpdate', true);
        }
        $this->validateOnSave($customer, true);
        $this->applyNamingScheme($customer);
    }

    public function postUpdate(CustomerInterface $customer)
    {
        if (!$this->isDisableSaveHandlers()) {
            $this->applySaveHandlers($customer, 'postUpdate');
        }

        if ($this->getSegmentBuildingHookEnabled()) {
            \Pimcore::getContainer()->get('cmf.segment_manager')->buildCalculatedSegmentsOnCustomerSave($customer);
        }

        if (!$this->isDisableQueue()) {
            \Pimcore::getContainer()->get('cmf.segment_manager')->addCustomerToChangesQueue($customer);
        }

        if (!$this->isDisableDuplicateIndex()) {
            \Pimcore::getContainer()->get('cmf.customer_duplicates_service')->updateDuplicateIndexForCustomer(
                $customer
            );
        }

    }

    public function preDelete(CustomerInterface $customer)
    {
        if (!$this->isDisableSaveHandlers()) {
            $this->applySaveHandlers($customer, 'preDelete', true);
        }
    }

    public function postDelete(CustomerInterface $customer)
    {
        if (!$this->isDisableSaveHandlers()) {
            $this->applySaveHandlers($customer, 'postDelete');
        }

        $this->addToDeletionsTable($customer);
    }

    public function validateOnSave(CustomerInterface $customer, $withDuplicatesCheck = true)
    {

        if (!$this->getCustomerSaveValidatorEnabled()) {
            return false;
        }

        /**
         * @var CustomerSaveValidatorInterface $validator
         */
        $validator = \Pimcore::getContainer()->get('cmf.customer_save_validator');

        return $validator->validate($customer, $withDuplicatesCheck);
    }

    protected function addToDeletionsTable(CustomerInterface $customer)
    {
        $db = Db::get();
        $db->insertOrUpdate(
            "plugin_cmf_deletions",
            [
                'id' => $customer->getId(),
                'creationDate' => time(),
                'entityType' => 'customers',
            ]
        );
    }

    protected function applySaveHandlers(CustomerInterface $customer, $saveHandlerMethod, $reinitSaveHandlers = false)
    {
        $saveHandlers = $this->createSaveHandlers();

        if ($reinitSaveHandlers) {
            $this->reinitSaveHandlers($saveHandlers, $customer);
        }


        foreach ($saveHandlers as $handler) {
            $this->getLogger()->debug(
                sprintf(
                    "apply save handler %s %s method to customer %s",
                    get_class($handler),
                    $saveHandlerMethod,
                    (string)$customer
                )
            );

            if ($saveHandlerMethod == 'preAdd') {
                $handler->preAdd($customer);
                $handler->preSave($customer);

            } elseif ($saveHandlerMethod == 'preUpdate') {
                $handler->preUpdate($customer);
                $handler->preSave($customer);

            } elseif ($saveHandlerMethod == 'postUpdate') {
                $handler->postUpdate($customer);
                $handler->postSave($customer);

            } elseif ($saveHandlerMethod == 'postAdd') {
                $handler->postAdd($customer);
                $handler->postSave($customer);

            } elseif ($saveHandlerMethod == 'preDelete') {
                $handler->preDelete($customer);

            } elseif ($saveHandlerMethod == 'postDelete') {
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
        foreach ($saveHandlers as $handler) {
            if ($handler->isOriginalCustomerNeeded()) {
                \Pimcore::collectGarbage();
                $originalCustomer = \Pimcore::getContainer()->get('cmf.customer_provider')->getById($customer->getId());
                break;
            }
        }

        if ($originalCustomer) {
            foreach ($saveHandlers as $handler) {
                if ($handler->isOriginalCustomerNeeded()) {
                    $handler->setOriginalCustomer($originalCustomer);
                }
            }
        }
    }

    /**
     * @return CustomerSaveHandlerInterface[]
     */
    protected function createSaveHandlers()
    {
        if (is_null($this->saveHandlers)) {
            $saveHandlers = [];
            foreach ($this->config->saveHandlers as $saveHandlerConfig) {

                $class = (string)$saveHandlerConfig->saveHandler;

                /**
                 * @var CustomerSaveHandlerInterface $saveHandler
                 */
                $saveHandler = Factory::getInstance()->createObject(
                    $class,
                    CustomerSaveHandlerInterface::class,
                    ["config" => $saveHandlerConfig, "logger" => $this->getLogger()]
                );
                $saveHandlers[] = $saveHandler;
            }

            $this->saveHandlers = $saveHandlers;
        }

        return $this->saveHandlers;
    }

    /**
     * @param CustomerInterface $customer
     * @param bool $disableVersions
     * @return mixed
     */
    public function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false)
    {
        $options = new \stdClass();
        $options->customerSaveValidatorEnabled = false;
        $options->segmentBuildingHookEnabled = false;

        return $this->saveWithOptions($customer, $options, $disableVersions);
    }

    /**
     * @param CustomerInterface $customer
     * @return mixed
     */
    function saveDirty(CustomerInterface $customer)
    {
        return $this->saveWithOptions($customer, $this->createDirtyOptions(), true);
    }

    /**
     * Disable all
     * @return \stdClass
     */
    protected function createDirtyOptions()
    {
        $options = new \stdClass();
        $options->customerSaveValidatorEnabled = false;
        $options->segmentBuildingHookEnabled = false;
        $options->disableSaveHandlers = true;
        $options->disableDuplicateIndex = true;
        $options->disableQueue = true;

        return $options;

    }

    /**
     * @param CustomerInterface $customer
     * @param \stdClass $options
     * @param bool $disableVersions
     * @return mixed
     */
    protected function saveWithOptions(CustomerInterface $customer, \stdClass $options, $disableVersions = false)
    {
        // retrieve default options
        $backupOptions = $this->getSaveOptions();
        // apply desired options
        $this->applySaveOptions($options);

        // backup current version option
        $versionsEnabled = !Version::$disabled;
        if ($disableVersions) {
            Version::disable();
        }

        try {
            return $customer->save();
        } finally {
            // restore version options
            if ($disableVersions && $versionsEnabled) {
                Version::enable();
            }

            // restore default options
            $this->applySaveOptions($backupOptions);
        }
    }

    /**
     * Backup options for later restore
     * @return \stdClass
     */
    protected function getSaveOptions()
    {
        $options = new \stdClass();
        $options->customerSaveValidatorEnabled = $this->getCustomerSaveValidatorEnabled();
        $options->segmentBuildingHookEnabled = $this->getSegmentBuildingHookEnabled();
        $options->disableSaveHandlers = $this->isDisableSaveHandlers();
        $options->disableDuplicateIndex = $this->isDisableDuplicateIndex();
        $options->disableQueue = $this->isDisableQueue();

        return $options;
    }

    /**
     * Restore options
     * @param \stdClass $options
     */
    protected function applySaveOptions(\stdClass $options)
    {
        if (isset($options->customerSaveValidatorEnabled) && $options->customerSaveValidatorEnabled !== $this->getCustomerSaveValidatorEnabled(
            )
        ) {
            $this->setCustomerSaveValidatorEnabled($options->customerSaveValidatorEnabled);
        }
        if (isset($options->segmentBuildingHookEnabled) && $options->segmentBuildingHookEnabled !== $this->getSegmentBuildingHookEnabled(
            )
        ) {
            $this->setSegmentBuildingHookEnabled($options->segmentBuildingHookEnabled);
        }
        if (isset($options->disableSaveHandlers) && $options->disableSaveHandlers !== $this->isDisableSaveHandlers()) {
            $this->setDisableSaveHandlers($options->disableSaveHandlers);
        }
        if (isset($options->disableDuplicateIndex) && $options->disableDuplicateIndex !== $this->isDisableDuplicateIndex(
            )
        ) {
            $this->setDisableDuplicateIndex($options->disableDuplicateIndex);
        }
        if (isset($options->disableQueue) && $options->disableQueue !== $this->isDisableQueue()) {
            $this->setDisableQueue($options->disableQueue);
        }
    }


}
