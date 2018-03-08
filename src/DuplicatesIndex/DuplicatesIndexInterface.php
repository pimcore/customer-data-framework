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

namespace CustomerManagementFrameworkBundle\DuplicatesIndex;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\Listing\Concrete;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Paginator\Paginator;

interface DuplicatesIndexInterface
{
    /**
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function recreateIndex();

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function updateDuplicateIndexForCustomer(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function deleteCustomerFromDuplicateIndex(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return bool
     */
    public function isRelevantForDuplicateIndex(CustomerInterface $customer);

    /**
     * @param OutputInterface $output
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
     * @param Concrete|null $filterCustomerList
     * @return Paginator
     */
    public function getPotentialDuplicates($page, $pageSize = 100, $declined = false, Concrete $filterCustomerList = null);

    /**
     * @param int $page
     * @param int $pageSize
     *
     * @return Paginator
     */
    public function getFalsePositives($page, $pageSize = 100);

    /**
     * @param int $id
     *
     * @return void
     */
    public function declinePotentialDuplicate($id);
}
