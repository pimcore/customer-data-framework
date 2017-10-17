<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

/**
 * @property SaveOptions $saveOptions
 */
trait LegacyTrait
{
    /**
     * @return bool
     *
     * @deprecated
     */
    public function getSegmentBuildingHookEnabled()
    {
        return $this->saveOptions->isOnSaveSegmentBuildersEnabled();
    }

    /**
     * @param bool $segmentBuildingHookEnabled
     *
     * @return $this
     *
     * @deprecated
     */
    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled)
    {
        if ($segmentBuildingHookEnabled) {
            $this->saveOptions->enableOnSaveSegmentBuilders();
        } else {
            $this->saveOptions->disableOnSaveSegmentBuilders();
        }

        return $this;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function getCustomerSaveValidatorEnabled()
    {
        return $this->saveOptions->isValidatorEnabled();
    }

    /**
     * @param bool $customerSaveValidatorEnabled
     *
     * @return $this
     *
     * @deprecated
     */
    public function setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled)
    {
        if ($customerSaveValidatorEnabled) {
            $this->saveOptions->enableValidator();
        } else {
            $this->saveOptions->disableValidator();
        }

        return $this;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function isDisableSaveHandlers()
    {
        return !$this->saveOptions->isSaveHandlersExecutionEnabled();
    }

    /**
     * @param bool $disableSaveHandlers
     *
     * @return $this
     *
     * @deprecated
     */
    public function setDisableSaveHandlers($disableSaveHandlers)
    {
        if ($disableSaveHandlers) {
            $this->saveOptions->disableSaveHandlers();
        } else {
            $this->saveOptions->enableSaveHandlers();
        }

        return $this;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function isDisableDuplicateIndex()
    {
        return !$this->saveOptions->isDuplicatesIndexEnabled();
    }

    /**
     * @param bool $disableDuplicateIndex
     *
     * @return $this
     *
     * @deprecated
     */
    public function setDisableDuplicateIndex($disableDuplicateIndex)
    {
        if ($disableDuplicateIndex) {
            $this->saveOptions->disableDuplicatesIndex();
        } else {
            $this->saveOptions->enableDuplicatesIndex();
        }

        return $this;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function isDisableQueue()
    {
        return !$this->saveOptions->isSegmentBuilderQueueEnabled();
    }

    /**
     * @param bool $disableQueue
     *
     * @return $this
     *
     * @deprecated
     */
    public function setDisableQueue($disableQueue)
    {
        if ($disableQueue) {
            $this->saveOptions->disableSegmentBuilderQueue();
        } else {
            $this->saveOptions->enableSegmentBuilderQueue();
        }

        return $this;
    }

    /**
     * @param CustomerInterface $customer
     * @param bool $disableVersions
     *
     * @return mixed
     *
     * @deprecated
     */
    public function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false)
    {
        /**
         * @var SaveOptions $options
         */
        $options = $this->getSaveOptions(true);
        $options
            ->disableValidator()
            ->disableOnSaveSegmentBuilders();

        return $this->saveWithOptions($customer, $options, $disableVersions);
    }
}
