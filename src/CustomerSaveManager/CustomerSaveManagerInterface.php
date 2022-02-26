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
    public function postAdd(CustomerInterface $customer);

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
     * @param CustomerInterface $customer
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

    /**
     * @param SaveOptions $saveOptions
     */
    public function setSaveOptions(SaveOptions $saveOptions);

    /**
     * @param CustomerInterface $customer
     * @param SaveOptions $options
     * @param bool $disableVersions
     *
     * @return mixed
     */
    public function saveWithOptions(CustomerInterface $customer, SaveOptions $options, $disableVersions = false);

    /**
     * Dirty / quick save customer w/o invoking any hooks, save-handlers, version and alike
     *
     * @param CustomerInterface $customer
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
     * @param CustomerSaveHandlerInterface $saveHandler
     *
     * @return void
     */
    public function addSaveHandler(CustomerSaveHandlerInterface $saveHandler);
}
