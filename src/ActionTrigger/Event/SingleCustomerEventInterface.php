<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ActionTrigger\Event;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface SingleCustomerEventInterface extends EventInterface
{
    public function __construct(CustomerInterface $customer);

    public function getCustomer();
}
