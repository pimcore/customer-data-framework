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
     * @return string
     */
    public function getNamingScheme();

    /**
     * @param string $namingScheme
     * @return void
     */
    public function setNamingScheme($namingScheme);
}
