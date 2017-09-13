<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Newsletter\Queue;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;

interface NewsletterQueueInterface
{
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';

    /**
     * @param CustomerInterface $customer
     * @param $operation
     * @param string|null $email
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
     * @param array $newsletterProviderHandler
     * @param NewsletterQueueItemInterface $newsletterQueueItem
     *
     * @return void
     */
    public function syncSingleQueueItem(array $newsletterProviderHandler, NewsletterQueueItemInterface $newsletterQueueItem);

    /**
     * @param NewsletterQueueItemInterface $item
     *
     * @return void
     */
    public function removeFromQueue(NewsletterQueueItemInterface $item);

    /**
     * @return void
     */
    public function enqueueAllCustomers();

    /**
     * @return int
     */
    public function getQueueSize();
}
