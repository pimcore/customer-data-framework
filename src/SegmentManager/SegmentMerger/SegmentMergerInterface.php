<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentMerger;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface SegmentMergerInterface
{
    /**
     * Could be used to add/remove segments to/from customers.
     * Take a look at the same method with the same name in the SegmentManagerInterface for further details.
     *
     * @param CustomerInterface $customer
     * @param array $addSegments
     * @param array $deleteSegments
     * @param string|null $hintForNotes
     * @param int|true|null $segmentCreatedTimestamp
     * @param int|true|null $segmentApplicationCounter
     *
     * @return void
     */
    public function mergeSegments(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $hintForNotes = null,
        $segmentCreatedTimestamp = null,
        $segmentApplicationCounter = null
    );

    /**
     * Needs to be called after segments are merged with mergeSegments() in order to persist the segments in the customer object
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function saveMergedSegments(CustomerInterface $customer);
}
