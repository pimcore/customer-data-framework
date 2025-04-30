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

namespace CustomerManagementFrameworkBundle\DuplicatesIndex;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Pimcore\Model\DataObject\Listing\Concrete;
use Symfony\Component\Console\Output\OutputInterface;

interface DuplicatesIndexInterface
{
    /**
     * @return void
     */
    public function recreateIndex();

    /**
     *
     * @return void
     */
    public function updateDuplicateIndexForCustomer(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function deleteCustomerFromDuplicateIndex(CustomerInterface $customer);

    /**
     *
     * @return bool
     */
    public function isRelevantForDuplicateIndex(CustomerInterface $customer);

    /**
     *
     * @return void
     */
    public function calculatePotentialDuplicates(OutputInterface $output);

    /**
     * @param bool $analyzeFalsePositives
     *
     * @return void
     */
    public function setAnalyzeFalsePositives($analyzeFalsePositives);

    /**
     * @return bool
     */
    public function getAnalyzeFalsePositives();

    /**
     * @param int $page
     * @param int $pageSize
     * @param bool $declined
     *
     * @return PaginationInterface
     */
    public function getPotentialDuplicates($page, $pageSize = 100, $declined = false, ?Concrete $filterCustomerList = null);

    /**
     * @param int $page
     * @param int $pageSize
     *
     * @return PaginationInterface
     */
    public function getFalsePositives($page, $pageSize = 100);

    /**
     * @param int $id
     *
     * @return void
     */
    public function declinePotentialDuplicate($id);
}
