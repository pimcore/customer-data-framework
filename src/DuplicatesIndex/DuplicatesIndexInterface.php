<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DuplicatesIndex;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
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
     *
     * @return Paginator
     */
    public function getPotentialDuplicates($page, $pageSize = 100, $declined = false);

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
