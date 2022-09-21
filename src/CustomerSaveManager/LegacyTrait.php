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

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

/**
 * @property SaveOptions $saveOptions
 *
 * @deprecated
 */
trait LegacyTrait
{
    /**
     * @return bool
     */
    public function getSegmentBuildingHookEnabled()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        return $this->saveOptions->isOnSaveSegmentBuildersEnabled();
    }

    /**
     * @param bool $segmentBuildingHookEnabled
     *
     * @return $this
     */
    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled)
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        if ($segmentBuildingHookEnabled) {
            $this->saveOptions->enableOnSaveSegmentBuilders();
        } else {
            $this->saveOptions->disableOnSaveSegmentBuilders();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getCustomerSaveValidatorEnabled()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        return $this->saveOptions->isValidatorEnabled();
    }

    /**
     * @param bool $customerSaveValidatorEnabled
     *
     * @return $this
     */
    public function setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled)
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        if ($customerSaveValidatorEnabled) {
            $this->saveOptions->enableValidator();
        } else {
            $this->saveOptions->disableValidator();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableSaveHandlers()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        return !$this->saveOptions->isSaveHandlersExecutionEnabled();
    }

    /**
     * @param bool $disableSaveHandlers
     *
     * @return $this
     */
    public function setDisableSaveHandlers($disableSaveHandlers)
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        if ($disableSaveHandlers) {
            $this->saveOptions->disableSaveHandlers();
        } else {
            $this->saveOptions->enableSaveHandlers();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableDuplicateIndex()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        return !$this->saveOptions->isDuplicatesIndexEnabled();
    }

    /**
     * @param bool $disableDuplicateIndex
     *
     * @return $this
     */
    public function setDisableDuplicateIndex($disableDuplicateIndex)
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        if ($disableDuplicateIndex) {
            $this->saveOptions->disableDuplicatesIndex();
        } else {
            $this->saveOptions->enableDuplicatesIndex();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isDisableQueue()
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        return !$this->saveOptions->isSegmentBuilderQueueEnabled();
    }

    /**
     * @param bool $disableQueue
     *
     * @return $this
     */
    public function setDisableQueue($disableQueue)
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

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
     */
    public function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false)
    {
        trigger_deprecation(
            'pimcore/customer-data-framework',
            '1.3.17',
            sprintf('%s is deprecated.', __METHOD__)
        );

        $options = $this->getSaveOptions(true);
        $options
            ->disableValidator()
            ->disableOnSaveSegmentBuilders();

        return $this->saveWithOptions($customer, $options, $disableVersions);
    }
}
