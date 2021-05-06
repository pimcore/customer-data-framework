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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentExtractor;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\DataObject\Data\ObjectMetadata;

interface SegmentExtractorInterface
{
    /**
     * @param CustomerInterface $customer
     *
     * @return CustomerSegmentInterface[]
     */
    public function getCalculatedSegmentsFromCustomer(CustomerInterface $customer);

    /**
     * @param CustomerInterface $customer
     *
     * @return CustomerSegmentInterface[]
     */
    public function getManualSegmentsFromCustomer(CustomerInterface $customer);

    /**
     * The CMF supports object with metadata and "normal" object relations as store for the segments of a customer.
     * This methods extracts the segments if object with metadata is used.
     *
     * @param CustomerSegmentInterface[]|ObjectMetadata[]|null $segments
     *
     * @return CustomerSegmentInterface[]
     */
    public function extractSegmentsFromPimcoreFieldData($segments): array;

    /**
     * returns an array with all segment application counters of all assiged segments of the customer
     *
     * @param CustomerInterface $customer
     *
     * @return int[] array key = segmentId, array value = segment application counter
     */
    public function getAllSegmentApplicationCounters(CustomerInterface $customer): array;

    /**
     * @param CustomerInterface $customer
     * @param CustomerSegmentInterface $customerSegment
     *
     * @return int
     */
    public function getSegmentApplicationCounter(CustomerInterface $customer, CustomerSegmentInterface $customerSegment): int;
}
