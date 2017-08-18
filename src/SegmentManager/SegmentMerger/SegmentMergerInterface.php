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
     * Could be used to add/remove segments to/from customers. If segments are added or removed this will be tracked in the notes/events tab of the customer. With the optional $hintForNotes parameter it's possible to add an iditional hint to the notes/event entries.
     * The changes of this method will be persisted when saveMergedSegments() gets called.
     *
     * @param CustomerInterface $customer
     * @param array $addSegments
     * @param array $deleteSegments
     * @param string|null $hintForNotes
     *
     * @return void
     */
    public function mergeSegments(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $hintForNotes = null
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
