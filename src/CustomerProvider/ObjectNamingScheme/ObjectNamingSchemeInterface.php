<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\CustomerProvider\ObjectNamingScheme;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface ObjectNamingSchemeInterface
{
    /**
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
