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

interface NewsletterManagerInterface
{
    /**
     * @param NewsletterAwareCustomerInterface $customer
     * @return void
     */
    public function subscribeCustomer(NewsletterAwareCustomerInterface $customer);

    /**
     * @param NewsletterAwareCustomerInterface $customer
     * @return void
     */
    public function unsubscribeCustomer(NewsletterAwareCustomerInterface $customer);

    /**
     * @return void
     */
    public function processSync( $changesQueueOnly = true );

    /**
     * @return void
     */
    public function syncSegments();

    /**
     * @return void
     */
    public function syncCustomers( $changesQueueOnly = true );

}