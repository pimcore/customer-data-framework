<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
