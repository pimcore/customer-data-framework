<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface ObjectNamingSchemeInterface
{
    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function apply(CustomerInterface $customer);

    /**
     * deletes empty subfolders of the customers folder
     *
     * @return void
     */
    public function cleanupEmptyFolders();

    /**
     * Returns the naming scheme format based on the given customer.
     *
     * example return string: {countryCode}/{zip}/{firstname}-{lastname}
     *
     * @return string
     */
    public function determineNamingScheme(CustomerInterface $customer);
}
