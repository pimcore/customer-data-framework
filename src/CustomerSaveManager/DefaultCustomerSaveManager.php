<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

use CustomerManagementFrameworkBundle\ActivityStore\ActivityStoreInterface;
use CustomerManagementFrameworkBundle\CustomerProvider\CustomerProviderInterface;
use CustomerManagementFrameworkBundle\CustomerSaveHandler\CustomerSaveHandlerInterface;
use CustomerManagementFrameworkBundle\CustomerSaveValidator\CustomerSaveValidatorInterface;
use CustomerManagementFrameworkBundle\DuplicatesIndex\DuplicatesIndexInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor\SegmentBuilderExecutorInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use CustomerManagementFrameworkBundle\Traits\PrimaryKeyTrait;
use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Db;
use Pimcore\Db\Helper;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\Element\ValidationException;
use Pimcore\Model\Version;
use Symfony\Component\HttpFoundation\RequestStack;

class DefaultCustomerSaveManager implements CustomerSaveManagerInterface
{
    use LoggerAware;
    use LegacyTrait;
    use PimcoreContextAwareTrait;
    use PrimaryKeyTrait;

    /**
     * @var SaveOptions
     */
    private $saveOptions;

    /**
     * @var SaveOptions
     */
    private $defaultSaveOptions;

    /**
     * @var CustomerSaveHandlerInterface[]
     */
    protected $saveHandlers = [];

    /**
     * @var CustomerProviderInterface
     */
    private $customerProvider;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CustomerInterface|null
     */
    private $originalCustomer;

    /**
     * @var ActivityStoreInterface
     */
    protected $activityStore;

    public function __construct(
        SaveOptions $saveOptions,
        CustomerProviderInterface $customerProvider,
        RequestStack $requestStack,
        ActivityStoreInterface $activityStore
    ) {
        $this->saveOptions = $saveOptions;
        $this->defaultSaveOptions = clone $saveOptions;
        $this->customerProvider = $customerProvider;
        $this->requestStack = $requestStack;
        $this->activityStore = $activityStore;
    }

    protected function applyNamingScheme(CustomerInterface $customer)
    {
        if ($this->saveOptions->isObjectNamingSchemeEnabled()) {
            $this->customerProvider->applyObjectNamingScheme($customer);

            $request = $this->requestStack->getMainRequest();

            //$this->setPimcoreContextResolver(\Pimcore::getContainer()->get('pimcore.service.request.pimcore_context_resolver'));

            if ($request && $this->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)) {
                if (!$customer->isAllowed('save') || ($customer->getPublished() && !$customer->isAllowed('publish'))) {
                    throw new ValidationException(sprintf('No permissions to save customer to folder "%s"', $customer->getParent()));
                }
            }
        }
    }

    protected function rememberOriginalCustomer(CustomerInterface $customer)
    {
        $originalCustomerNeeded = false;
        if ($this->saveOptions->isSaveHandlersExecutionEnabled()) {
            foreach ($this->getSaveHandlers() as $saveHandler) {
                if ($saveHandler->isOriginalCustomerNeeded()) {
                    $originalCustomerNeeded = true;
                    break;
                }
            }
        }

        if (!$originalCustomerNeeded && $this->getSaveOptions()->isNewsletterQueueEnabled() && $customer->getId() != null) {
            $originalCustomerNeeded = true;
        }

        $originalCustomer = $this->originalCustomer;

        if ($originalCustomer && ($originalCustomer->getId() != $customer->getId())) {
            $originalCustomer = null;
        }

        if ($originalCustomerNeeded) {
            $originalCustomer = $this->customerProvider->getById($customer->getId(), true);
        }

        $this->originalCustomer = $originalCustomer;
    }

    public function preAdd(CustomerInterface $customer)
    {
        $this->rememberOriginalCustomer($customer);

        if ($this->saveOptions->isSaveHandlersExecutionEnabled()) {
            $this->applySaveHandlers($customer, 'preAdd', true);
        }

        if ($customer->getPublished()) {
            $this->validateOnSave($customer);
        }

        $request = $this->requestStack->getMainRequest();

        //$this->setPimcoreContextResolver(\Pimcore::getContainer()->get('pimcore.service.request.pimcore_context_resolver'));

        if (!$request || !$this->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)) {
            $this->applyNamingScheme($customer);
        }
    }

    public function postAdd(CustomerInterface $customer)
    {
        if ($this->saveOptions->isOnSaveSegmentBuildersEnabled()) {
            \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class)->buildCalculatedSegmentsOnCustomerSave($customer);
        }

        if ($this->saveOptions->isSaveHandlersExecutionEnabled()) {
            $this->applySaveHandlers($customer, 'postAdd');
        }

        if ($this->saveOptions->isSegmentBuilderQueueEnabled()) {
            \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class)->addCustomerToChangesQueue($customer);
        }

        if ($this->saveOptions->isDuplicatesIndexEnabled()) {
            \Pimcore::getContainer()->get('cmf.customer_duplicates_service')->updateDuplicateIndexForCustomer(
                $customer
            );
        }

        $this->handleNewsletterQueue($customer, NewsletterQueueInterface::OPERATION_UPDATE);
    }

    public function preUpdate(CustomerInterface $customer)
    {
        $this->rememberOriginalCustomer($customer);

        if (!$customer->getIdEncoded()) {
            $customer->setIdEncoded(md5((string) $customer->getId()));
        }

        if ($this->saveOptions->isSaveHandlersExecutionEnabled()) {
            $this->applySaveHandlers($customer, 'preUpdate', true);
        }
        $this->validateOnSave($customer, true);
        $this->applyNamingScheme($customer);
    }

    public function postUpdate(CustomerInterface $customer)
    {
        if ($this->saveOptions->isSaveHandlersExecutionEnabled()) {
            $this->applySaveHandlers($customer, 'postUpdate');
        }

        if ($this->saveOptions->isOnSaveSegmentBuildersEnabled()) {
            \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class)->buildCalculatedSegmentsOnCustomerSave($customer);
        }

        if ($this->saveOptions->isSegmentBuilderQueueEnabled()) {
            \Pimcore::getContainer()->get(SegmentBuilderExecutorInterface::class)->addCustomerToChangesQueue($customer);
        }

        if ($this->saveOptions->isDuplicatesIndexEnabled()) {
            \Pimcore::getContainer()->get('cmf.customer_duplicates_service')->updateDuplicateIndexForCustomer(
                $customer
            );
        }

        $this->handleNewsletterQueue($customer, NewsletterQueueInterface::OPERATION_UPDATE);
    }

    public function preDelete(CustomerInterface $customer)
    {
        $this->rememberOriginalCustomer($customer);

        if (!$this->saveOptions->isSaveHandlersExecutionEnabled()) {
            $this->applySaveHandlers($customer, 'preDelete', true);
        }
    }

    public function postDelete(CustomerInterface $customer)
    {
        if (!$this->saveOptions->isSaveHandlersExecutionEnabled()) {
            $this->applySaveHandlers($customer, 'postDelete');
        }

        $this->addToDeletionsTable($customer);

        $this->handleNewsletterQueue($customer, NewsletterQueueInterface::OPERATION_DELETE);

        /**
         * @var DuplicatesIndexInterface $duplicatesIndex
         */
        $duplicatesIndex = \Pimcore::getContainer()->get(DuplicatesIndexInterface::class);
        $duplicatesIndex->deleteCustomerFromDuplicateIndex($customer);

        $this->activityStore->deleteCustomer($customer);
    }

    public function validateOnSave(CustomerInterface $customer, $withDuplicatesCheck = true)
    {
        if (!$this->saveOptions->isValidatorEnabled()) {
            return false;
        }

        /**
         * @var CustomerSaveValidatorInterface $validator
         */
        $validator = \Pimcore::getContainer()->get('cmf.customer_save_validator');

        return $validator->validate($customer, $withDuplicatesCheck);
    }

    /**
     * @param CustomerInterface $customer
     * @param string $operation
     */
    protected function handleNewsletterQueue(CustomerInterface $customer, $operation)
    {
        if ($customer instanceof NewsletterAwareCustomerInterface && $this->saveOptions->isNewsletterQueueEnabled()) {
            /** @var NewsletterQueueInterface $newsletterQueue */
            $newsletterQueue = \Pimcore::getContainer()->get('cmf.newsletter.queue');
            $newsletterQueue->enqueueCustomer($customer, $operation, $this->originalCustomer ? $this->originalCustomer->getEmail() : $customer->getEmail(), $this->saveOptions->isNewsletterQueueImmediateAsyncExecutionEnabled());
        }
    }

    protected function addToDeletionsTable(CustomerInterface $customer)
    {
        $db = Db::get();
        Helper::upsert(
            $db,
            'plugin_cmf_deletions',
            [
                'id' => $customer->getId(),
                'creationDate' => time(),
                'entityType' => 'customers',
            ],
            $this->getPrimaryKey($db, 'plugin_cmf_deletions')
        );
    }

    protected function applySaveHandlers(CustomerInterface $customer, $saveHandlerMethod, $reinitSaveHandlers = false)
    {
        $saveHandlers = $this->getSaveHandlers();

        if ($reinitSaveHandlers) {
            $this->reinitSaveHandlers($saveHandlers, $customer);
        }

        foreach ($saveHandlers as $handler) {
            $this->getLogger()->debug(
                sprintf(
                    'apply save handler %s %s method to customer %s',
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
        foreach ($saveHandlers as $handler) {
            if (!$handler->isOriginalCustomerNeeded()) {
                continue;
            }

            $handler->setOriginalCustomer($this->originalCustomer);
        }
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return mixed
     */
    public function saveDirty(CustomerInterface $customer, $disableVersions = true)
    {
        return $this->saveWithOptions($customer, $this->createDirtyOptions(), $disableVersions);
    }

    /**
     * @return CustomerSaveHandlerInterface[]
     */
    public function getSaveHandlers()
    {
        return $this->saveHandlers;
    }

    /**
     * @param CustomerSaveHandlerInterface[] $saveHandlers
     */
    public function setSaveHandlers(array $saveHandlers)
    {
        $this->saveHandlers = $saveHandlers;
    }

    public function addSaveHandler(CustomerSaveHandlerInterface $saveHandler)
    {
        $this->saveHandlers[] = $saveHandler;
    }

    /**
     * Disable all
     *
     * @return SaveOptions
     */
    protected function createDirtyOptions()
    {
        return new SaveOptions();
    }

    /**
     * @param CustomerInterface $customer
     * @param SaveOptions $options
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveWithOptions(CustomerInterface $customer, SaveOptions $options, $disableVersions = false)
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
     * @param bool $clone
     *
     * @return SaveOptions
     */
    public function getSaveOptions($clone = false)
    {
        if ($clone) {
            return clone $this->saveOptions;
        }

        return $this->saveOptions;
    }

    public function setSaveOptions(SaveOptions $saveOptions)
    {
        $this->saveOptions = $saveOptions;
    }

    public function getDefaultSaveOptions()
    {
        return clone $this->defaultSaveOptions;
    }

    /**
     * Restore options
     *
     * @param SaveOptions $options
     */
    protected function applySaveOptions(SaveOptions $options)
    {
        $this->saveOptions = $options;
    }

    /**
     * @return CustomerInterface|null
     */
    protected function getOriginalCustomer()
    {
        return $this->originalCustomer;
    }
}
