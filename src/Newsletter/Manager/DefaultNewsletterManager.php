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
use CustomerManagementFrameworkBundle\Newsletter\Exception\SubscribeFailedException;
use CustomerManagementFrameworkBundle\Newsletter\Exception\UnsubscribeFailedException;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\Item\NewsletterQueueItemInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;

class DefaultNewsletterManager implements NewsletterManagerInterface
{
    /**
     * @var NewsletterProviderHandlerInterface[]
     */
    protected $newsletterProviderHandlers = [];

    /**
     * Subscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool
     *
     * @throws SubscribeFailedException
     */
    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        $success = true;
        foreach ($this->newsletterProviderHandlers as $newsletterProviderHandler) {
            if (!$newsletterProviderHandler->subscribeCustomer($customer)) {
                $success = false;
                break;
            }
        }

        if (!$success) {
            throw new SubscribeFailedException(sprintf('newsletter subscribe of customer ID %s failed', $customer->getId()));
        }

        return true;
    }

    /**
     * Unsubscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
     *
     * @return bool
     *
     * @throws UnsubscribeFailedException
     */
    public function unsubscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        $success = true;
        foreach ($this->newsletterProviderHandlers as $newsletterProviderHandler) {
            if (!$newsletterProviderHandler->unsubscribeCustomer($customer)) {
                $success = false;
                break;
            }
        }

        if (!$success) {
            throw new UnsubscribeFailedException(sprintf('newsletter unsubscribe of customer ID %s failed', $customer->getId()));
        }

        return true;
    }

    public function syncSegments($forceUpdate = false)
    {
        foreach ($this->newsletterProviderHandlers as $newsletterProviderHandler) {
            $newsletterProviderHandler->updateSegmentGroups($forceUpdate);
        }
    }

    public function syncCustomers($forceAllCustomers = false, $forceUpdate = false)
    {
        /**
         * @var NewsletterQueueInterface $queue
         */
        $queue = \Pimcore::getContainer()->get(NewsletterQueueInterface::class);

        $queue->processQueue($this->newsletterProviderHandlers, $forceAllCustomers, $forceUpdate);
    }

    public function syncSingleCustomerQueueItem(NewsletterQueueItemInterface $newsletterQueueItem)
    {
        /**
         * @var NewsletterQueueInterface $queue
         */
        $queue = \Pimcore::getContainer()->get(NewsletterQueueInterface::class);

        $queue->syncSingleQueueItem($this->getNewsletterProviderHandlers(), $newsletterQueueItem);
    }

    /**
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
     *
     * @return void
     */
    public function addNewsletterProviderHandler(NewsletterProviderHandlerInterface $newsletterProviderHandler)
    {
        $this->newsletterProviderHandlers[] = $newsletterProviderHandler;
    }

    /**
     * @return NewsletterProviderHandlerInterface[]
     */
    public function getNewsletterProviderHandlers()
    {
        return $this->newsletterProviderHandlers;
    }
}
