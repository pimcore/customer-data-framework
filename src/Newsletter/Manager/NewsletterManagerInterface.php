<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
     * @return bool
     */
    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer);

    /**
     * Unsubscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
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
    public function syncCustomers( $forceAllCustomers = false, $forceUpdate = false );

    /**
     * @param NewsletterQueueItemInterface $newsletterQueueItem
     * @return void
     */
    public function syncSingleCustomerQueueItem(NewsletterQueueItemInterface $newsletterQueueItem);

    /**
     * @param string $shortcut
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
     * @return void
     */
    public function addNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler);

    /**
     * @return NewsletterProviderHandlerInterface[]
     */
    public function getNewsletterProviderHandlers();

}