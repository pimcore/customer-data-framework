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

class DefaultSegmentExtractor implements SegmentExtractorInterface
{
    /**
     * @inheritdoc
     */
    public function getCalculatedSegmentsFromCustomer(CustomerInterface $customer)
    {
        return $this->extractSegmentsFromPimcoreFieldData($customer->getCalculatedSegments());
    }

    public function getManualSegmentsFromCustomer(CustomerInterface $customer)
    {
        return $this->extractSegmentsFromPimcoreFieldData($customer->getManualSegments());
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getAllSegmentApplicationCounters(CustomerInterface $customer): array
    {
        $segments = [];
        $segments = $this->extractSegmentApplicationCountersFromPimcoreFieldData($customer->getManualSegments(), $segments);
        $segments = $this->extractSegmentApplicationCountersFromPimcoreFieldData($customer->getCalculatedSegments(), $segments);

        return $segments;
    }

    /**
     * @inheritdoc
     */
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
