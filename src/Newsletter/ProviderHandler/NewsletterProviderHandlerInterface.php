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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler;

use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;

interface NewsletterProviderHandlerInterface
{
    /**
     * Returns a unique identifier/short name of the provider handler.
     *
     * @return string
     */
    public function getShortcut();

    /**
     * Update given NewsletterQueueItems in newsletter provider.
     * Needs to set $item->setSuccsessfullyProcessed(true) if it was successfull otherwise the item will never be removed from the newsletter queue.
     *
     * @param NewsletterQueueItemInterface[] $items
     * @param bool $forceUpdate
     *
     * @return void
     */
    public function processCustomerQueueItems(array $items, $forceUpdate = false);

    /**
     * @param bool $forceUpdate
     *
     * @return void
     */
    public function updateSegmentGroups($forceUpdate = false);

    /**
     * Subscribe customer to newsletter (for example via web form). Returns true if it was successful.
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
}
