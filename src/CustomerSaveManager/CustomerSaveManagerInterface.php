<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\CustomerSaveManager;

use CustomerManagementFrameworkBundle\CustomerSaveHandler\CustomerSaveHandlerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface CustomerSaveManagerInterface
{
    /**
     *
     * @return void
     */
    public function preAdd(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function postAdd(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function preUpdate(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function postUpdate(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function preDelete(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function postDelete(CustomerInterface $customer);

    /**
     * @param bool $withDuplicatesCheck
     *
     * @return bool
     */
    public function validateOnSave(CustomerInterface $customer, $withDuplicatesCheck = true);

    /**
     * @return SaveOptions
     */
    public function getDefaultSaveOptions();

    /**
     * @param bool $clone
     *
     * @return SaveOptions
     */
    public function getSaveOptions($clone = false);

    public function setSaveOptions(SaveOptions $saveOptions);

    /**
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveWithOptions(CustomerInterface $customer, SaveOptions $options, $disableVersions = false);

    /**
     * Dirty / quick save customer w/o invoking any hooks, save-handlers, version and alike
     *
     *
     * @return mixed
     */
    public function saveDirty(CustomerInterface $customer, $disableVersions = true);

    /**
     * @return CustomerSaveHandlerInterface[]
     */
    public function getSaveHandlers();

    /**
     * @param CustomerSaveHandlerInterface[] $saveHandlers
     */
    public function setSaveHandlers(array $saveHandlers);

    /**
     *
     * @return void
     */
    public function addSaveHandler(CustomerSaveHandlerInterface $saveHandler);
}
