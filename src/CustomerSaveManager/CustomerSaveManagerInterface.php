<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

use CustomerManagementFrameworkBundle\CustomerSaveHandler\CustomerSaveHandlerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface CustomerSaveManagerInterface
{
    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preAdd(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preUpdate(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function postUpdate(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function preDelete(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function postDelete(CustomerInterface $customer);

    /**
     * @param bool $segmentBuildingHookEnabled
     *
     * @return void
     */
    public function setSegmentBuildingHookEnabled($segmentBuildingHookEnabled);

    /**
     * @return bool
     */
    public function getSegmentBuildingHookEnabled();

    /**
     * @return bool
     */
    public function getCustomerSaveValidatorEnabled();

    /**
     * @param bool $customerSaveValidatorEnabled
     */
    public function setCustomerSaveValidatorEnabled($customerSaveValidatorEnabled);

    /**
     * @param CustomerInterface $customer
     * @param bool $withDuplicatesCheck
     *
     * @return bool
     */
    public function validateOnSave(CustomerInterface $customer, $withDuplicatesCheck = true);

    /**
     * Saves customer with disabled segment builder + customer save validator
     *
     * @param CustomerInterface $customer
     * @param bool $disableVersions
     *
     * @return void
     */
    public function saveWithDisabledHooks(CustomerInterface $customer, $disableVersions = false);

    /**
     * Dirty / quick save customer w/o invoking any hooks, save-handlers, version and alike
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function saveDirty(CustomerInterface $customer);

    /**
     * @return CustomerSaveHandlerInterface[]
     */
    public function getSaveHandlers();

    /**
     * @param CustomerSaveHandlerInterface[] $saveHandlers
     */
    public function setSaveHandlers(array $saveHandlers);

    /**
     * @param CustomerSaveHandlerInterface $saveHandler
     *
     * @return void
     */
    public function addSaveHandler(CustomerSaveHandlerInterface $saveHandler);

    /**
     * @return bool
     */
    public function getEnableAutomaticObjectNamingScheme();

    /**
     * @param bool $enableAutomaticObjectNamingScheme
     */
    public function setEnableAutomaticObjectNamingScheme($enableAutomaticObjectNamingScheme);
}
