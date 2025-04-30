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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentExtractor;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\DataObject\Data\ObjectMetadata;

interface SegmentExtractorInterface
{
    /**
     *
     * @return CustomerSegmentInterface[]
     */
    public function getCalculatedSegmentsFromCustomer(CustomerInterface $customer);

    /**
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
     *
     * @return int[] array key = segmentId, array value = segment application counter
     */
    public function getAllSegmentApplicationCounters(CustomerInterface $customer): array;

    public function getSegmentApplicationCounter(CustomerInterface $customer, CustomerSegmentInterface $customerSegment): int;
}
