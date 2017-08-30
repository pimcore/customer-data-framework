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
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;

class DefaultNewsletterManager implements NewsletterManagerInterface
{
    /**
     * @var SegmentManagerInterface
     */
    private $segmentManager;

    /**
     * @var NewsletterProviderHandlerInterface
     */
    private $newsletterProviderHandler;

    public function __construct(SegmentManagerInterface $segmentManager, NewsletterProviderHandlerInterface $newsletterProviderHandler)
    {
        $this->segmentManager = $segmentManager;

        if(is_null($newsletterProviderHandler)) {
            throw new \Exception('No newsletter provider handler configured. Take a look at the CMF docs newsletter section.');
        }

        $this->newsletterProviderHandler = $newsletterProviderHandler;
    }

    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        $customer->setNewsletter(true);
        $customer->setNewsletterUnsubscriptionDate(null);
        $customer->save();
    }

    public function unsubscribeCustomer(NewsletterAwareCustomerInterface $customer)
    {
        $customer->setNewsletter(false);
        $customer->setNewsletterUnsubscriptionDate(new Carbon());
        $customer->setNewsletterDataMd5(null);
        $customer->save();
    }

    public function processSync($changesQueueOnly = true)
    {
        $this->syncSegments();

        $this->syncCustomers($changesQueueOnly);
    }

    public function syncSegments()
    {
        $segmentGroups = $this->segmentManager->getSegmentGroups();
        $segmentGroups->addConditionParam("exportNewsletterProvider = 1");

        $this->newsletterProviderHandler->updateSegmentGroups($segmentGroups->load());
    }

    public function syncCustomers($changesQueueOnly = true)
    {
        // TODO: Implement processSyncCustomers() method.
    }
}