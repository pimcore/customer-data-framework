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

namespace CustomerManagementFrameworkBundle\Newsletter\Manager;

use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;

interface NewsletterManagerInterface
{
    /**
     * Subscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool
     */
    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer);

    /**
     * Unsubscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool
     */
    public function unsubscribeCustomer(NewsletterAwareCustomerInterface $customer);

    /**
     * @return void
     */
    public function syncSegments($forceUpdate = false);

    /**
     * @return void
     */
    public function syncCustomers($forceAllCustomers = false, $forceUpdate = false);

    /**
     * @param NewsletterQueueItemInterface $newsletterQueueItem
     *
     * @return void
     */
    public function syncSingleCustomerQueueItem(NewsletterQueueItemInterface $newsletterQueueItem);

    /**
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
     *
     * @return void
     */
    public function addNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler);

    /**
     * @return NewsletterProviderHandlerInterface[]
     */
    public function getNewsletterProviderHandlers();
}
