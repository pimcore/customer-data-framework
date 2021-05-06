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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentMerger\DefaultSegmentMerger;

use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentMerger\DefaultSegmentMerger;
use Pimcore\Model\DataObject\Data\ObjectMetadata;

class MetadataFiller
{
    /**
     * @param CustomerInterface $customer
     * @param array $addSegments
     * @param array $addSegments
     * @param bool $calculated
     * @param int|true|null $segmentCreatedTimestamp
     * @param int|true|null $segmentApplicationCounter
     * @param bool $saveNeeded
     */
    public function mergedSegmentsFillUpMetadata(
        CustomerInterface $customer,
        array $addSegments,
        array $addedSegments,
        $calculated,
        $segmentCreatedTimestamp,
        $segmentApplicationCounter,
        $saveNeeded,
        DefaultSegmentMerger $segmentMerger
    ) {
        if (!$segmentMerger->hasObjectMetadataSegmentsField($calculated)) {
            return $saveNeeded;
        }

        $timestampIfSegmentIsNew = $this->determineTimestampIfSegmentIsNew($segmentCreatedTimestamp);

        /**
         * @var ObjectMetadata $segmentItem
         */
        foreach ($segmentMerger->getSegmentsDataFromCustomer($customer, $calculated) as $segmentItem) {
            if (!Objects::objectInArray($segmentItem, $addSegments)) {
                continue;
            }

            $appliedTimestamp = $this->determineAppliedTimestamp($segmentItem, $addedSegments, $timestampIfSegmentIsNew, $segmentCreatedTimestamp);

            if ($appliedTimestamp !== $this->getCurrentTimestamp($segmentItem)) {
                $saveNeeded = true;
            }

            $appliedCounter = $this->determineAppliedCounter($segmentItem, $addedSegments, $appliedTimestamp, $segmentApplicationCounter);

            if ($appliedCounter !== $this->getCurrentCounter($segmentItem)) {
                $saveNeeded = true;
            }

            $segmentItem->setCreated_timestamp($appliedTimestamp);
            $segmentItem->setApplication_counter($appliedCounter);
        }

        return $saveNeeded;
    }

    protected function determineTimestampIfSegmentIsNew($segmentCreatedTimestamp)
    {
        $timestampIfSegmentIsNew = $segmentCreatedTimestamp === true ? time() : $segmentCreatedTimestamp;

        if (is_int($timestampIfSegmentIsNew)) {
            return $timestampIfSegmentIsNew;
        }

        return null;
    }

    protected function getCurrentTimestamp(ObjectMetadata $segmentItem)
    {
        return $segmentItem->getCreated_timestamp();
    }

    protected function getCurrentCounter(ObjectMetadata $segmentItem)
    {
        return $segmentItem->getApplication_counter();
    }

    protected function determineAppliedTimestamp(ObjectMetadata $segmentItem, $addedSegments, $timestampIfSegmentIsNew, $segmentCreatedTimestamp)
    {
        if (is_int($segmentCreatedTimestamp)) {
            return $segmentCreatedTimestamp;
        }

        if (Objects::objectInArray($segmentItem, $addedSegments)) {
            return $timestampIfSegmentIsNew;
        }

        if ($segmentCreatedTimestamp === true) {
            return $this->getCurrentTimestamp($segmentItem);
        }

        return null;
    }

    protected function determineAppliedCounter(ObjectMetadata $segmentItem, $addedSegments, $appliedTimestamp, $segmentApplicationCounter)
    {
        if (is_int($segmentApplicationCounter) || is_null($segmentApplicationCounter)) {
            return $segmentApplicationCounter;
        }

        if ($segmentApplicationCounter === true) {
            $currentCounter = intval($this->getCurrentCounter($segmentItem));

            if ($appliedTimestamp != $this->getCurrentTimestamp($segmentItem)) {
                return $currentCounter + 1;
            }

            return $currentCounter > 0 ? $currentCounter : 1;
        }

        return null;
    }
}
