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

interface NewsletterQueueInterface
{
    /**
     * @param CustomerInterface $customer
     * @param $operation
     * @param string|null $email
     *
     * @return void
     */
    public function enqueueCustomer(CustomerInterface $customer, $operation, $email = null);
}
