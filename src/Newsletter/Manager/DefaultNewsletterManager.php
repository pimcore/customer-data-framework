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

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\Model\NewsletterAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterProviderHandlerInterface;
use CustomerManagementFrameworkBundle\Newsletter\Queue\NewsletterQueueInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;

class DefaultNewsletterManager implements NewsletterManagerInterface
{
    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var NewsletterProviderHandlerInterface[]
     */
    protected $newsletterProviderHandlers = [];

    public function __construct(SegmentManagerInterface $segmentManager)
    {
        $this->segmentManager = $segmentManager;
    }

    /**
     * Subscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
     * @return bool
     */
    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        foreach ($this->newsletterProviderHandlers as $newsletterProviderHandler) {
            $newsletterProviderHandler->subscribeCustomer($customer);
        }
    }


    /**
     * Unsubscribe customer from newsletter (for example via web form). Returns true if it was successful.
     *
     * @param NewsletterAwareCustomerInterface $customer
     * @return bool
     */
    public function unsubscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        foreach ($this->newsletterProviderHandlers as $newsletterProviderHandler) {
            $newsletterProviderHandler->unsubscribeCustomer($customer);
        }
    }


    public function syncSegments($forceUpdate = false)
    {
        foreach ($this->newsletterProviderHandlers as $newsletterProviderHandler) {
            $newsletterProviderHandler->updateSegmentGroups($forceUpdate);
        }
    }

    public function syncCustomers( $forceAllCustomers = false, $forceUpdate = false )
    {
        /**
         * @var NewsletterQueueInterface $queue
         */
        $queue = \Pimcore::getContainer()->get(NewsletterQueueInterface::class );

        $queue->processQueue($this->newsletterProviderHandlers, $forceAllCustomers, $forceUpdate);
    }

    /**
     * @param string $shortcut
     * @param NewsletterProviderHandlerInterface $newsletterProviderHandler
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