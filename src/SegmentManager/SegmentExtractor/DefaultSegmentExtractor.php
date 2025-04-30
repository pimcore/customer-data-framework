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

class DefaultSegmentExtractor implements SegmentExtractorInterface
{
    public function getCalculatedSegmentsFromCustomer(CustomerInterface $customer)
    {
        return $this->extractSegmentsFromPimcoreFieldData($customer->getCalculatedSegments());
    }

    public function getManualSegmentsFromCustomer(CustomerInterface $customer)
    {
        return $this->extractSegmentsFromPimcoreFieldData($customer->getManualSegments());
    }

    public function extractSegmentsFromPimcoreFieldData($segments): array
    {
        if (!is_array($segments) || empty($segments)) {
            return [];
        }
        $result = [];
        foreach ($segments as $segment) {
            if ($segment instanceof CustomerSegmentInterface) {
                $result[] = $segment;
            } elseif ($segment instanceof ObjectMetadata && $segment->getObject() instanceof CustomerSegmentInterface) {
                $result[] = $segment->getObject();
            }
        }

        return $result;
    }

    public function getAllSegmentApplicationCounters(CustomerInterface $customer): array
    {
        $segments = [];
        $segments = $this->extractSegmentApplicationCountersFromPimcoreFieldData($customer->getManualSegments(), $segments);
        $segments = $this->extractSegmentApplicationCountersFromPimcoreFieldData($customer->getCalculatedSegments(), $segments);

        return $segments;
    }

    public function getSegmentApplicationCounter(CustomerInterface $customer, CustomerSegmentInterface $customerSegment): int
    {
        $allCounters = $this->getAllSegmentApplicationCounters($customer);

        return isset($allCounters[$customerSegment->getId()]) ? $allCounters[$customerSegment->getId()] : 0;
    }

    private function extractSegmentApplicationCountersFromPimcoreFieldData($field, array $segments = []): array
    {
        if (!is_array($field) || empty($field)) {
            return $segments;
        }
        foreach ($field as $segment) {
            $segmentId = null;
            $count = 1;
            if ($segment instanceof CustomerSegmentInterface) {
                $segmentId = $segment->getId();
            } elseif ($segment instanceof ObjectMetadata && $segment->getObject() instanceof CustomerSegmentInterface) {
                $segmentId = $segment->getObject()->getId();
                $count = $segment->getApplication_counter();
                if (is_numeric($count)) {
                    $count = (int)$count;
                } else {
                    $count = 1;
                }
            }
            if (!isset($segments[$segmentId])) {
                $segments[$segmentId] = 0;
            }
            $segments[$segmentId] += $count;
        }

        return $segments;
    }
}
