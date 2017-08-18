<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace AppBundle\Model;

use CustomerManagementFrameworkBundle\Model\AbstractCustomer\DefaultAbstractUserawareCustomer;
use CustomerManagementFrameworkBundle\Model\SsoAwareCustomerInterface;

abstract class Customer extends DefaultAbstractUserawareCustomer implements SsoAwareCustomerInterface
{
}
