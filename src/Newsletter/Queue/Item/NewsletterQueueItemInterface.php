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

namespace CustomerManagementFrameworkBundle\Newsletter\Queue\Item;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface NewsletterQueueItemInterface
{
    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @return CustomerInterface|null
     */
    public function getCustomer();

    /**
     * @return string|null
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getOperation();

    /**
     * @return int
     */
    public function getModificationDate();

    /**
     * @param bool $successfullyProcessed
     *
     * @return void
     */
    public function setSuccessfullyProcessed($successfullyProcessed);

    /**
     * @return bool
     */
    public function wasSuccessfullyProcessed();

    /**
     * Could be used as a storage for an overruled operation.
     * If an update item is in the queue but the related customer is excluded by $customer->needsExportByNewsletterProviderHandler($providerHandler)
     * the customer needs to be deleted allthough it is an update operation in the queue.
     *
     * @return string
     */
    public function getOverruledOperation();

    /**
     * Could be used as a storage for an overruled operation.
     * If an update item is in the queue but the related customer is excluded by $customer->needsExportByNewsletterProviderHandler($providerHandler)
     * the customer needs to be deleted allthough it is an update operation in the queue.
     *
     * @param string|null $overruledOperation
     */
    public function setOverruledOperation($overruledOperation);

    /**
     * @return string
     */
    public function toJson();
}
