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

namespace CustomerManagementFrameworkBundle\Newsletter\Queue;

use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;

interface NewsletterQueueInterface
{
    const OPERATION_UPDATE = 'update';

    const OPERATION_DELETE = 'delete';

    /**
     * @param string $operation
     * @param string|null $email
     * @param bool $immediateAsyncProcessQueueItem
     *
     * @return void
     */
    public function enqueueCustomer(NewsletterAwareCustomerInterface $customer, $operation, $email = null, $immediateAsyncProcessQueueItem = false);

    /**
     * @param NewsletterProviderHandlerInterface[] $newsletterProviderHandler
     * @param bool $forceAllCustomers
     * @param bool $forceUpdate
     *
     * @return void
     */
    public function processQueue(array $newsletterProviderHandler, $forceAllCustomers = false, $forceUpdate = false);

    /**
     *
     * @return void
     */
    public function syncSingleQueueItem(array $newsletterProviderHandler, NewsletterQueueItemInterface $newsletterQueueItem);

    /**
     *
     * @return void
     */
    public function removeFromQueue(NewsletterQueueItemInterface $item);

    /**
     * @return void
     */
    public function executeImmidiateAsyncQueueItems();

    /**
     * @return void
     */
    public function enqueueAllCustomers();

    /**
     * @return int
     */
    public function getQueueSize();
}
