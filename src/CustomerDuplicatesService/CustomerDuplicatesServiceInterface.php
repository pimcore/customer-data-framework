<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomerDuplicatesService;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface CustomerDuplicatesServiceInterface
{
    /**
     * Returns a list of duplicates for the given customer.
     * Which fields should be used for matching duplicates could be defined in the config of the CMF plugin.
     *
     * @param CustomerInterface $customer
     * @param int $limit
     *
     * @return \Pimcore\Model\DataObject\Listing\Concrete|null
     */
    public function getDuplicatesOfCustomer(CustomerInterface $customer, $limit = 0);

    /**
     * Returns a list of duplicates for the given customer. Duplicates are matched by the fields given in $fields.
     *
     * @param CustomerInterface $customer
     * @param array $fields
     * @param int $limit
     *
     * @return \Pimcore\Model\DataObject\Listing\Concrete|null
     */
    public function getDuplicatesOfCustomerByFields(CustomerInterface $customer, array $fields, $limit = 0);

    /**
     * Returns a list of duplicates/customers which are matching the given data.
     *
     * @param array $data
     * @param int $limit
     *
     * @return \Pimcore\Model\DataObject\Listing\Concrete|null
     */
    public function getDuplicatesByData(array $data, $limit = 0);

    /**
     * Returns which field combination matched the last found duplicates.
     *
     * @return array
     */
    public function getMatchedDuplicateFields();

    /**
     * Update the duplicate index for the given customer.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function updateDuplicateIndexForCustomer(CustomerInterface $customer);
}
