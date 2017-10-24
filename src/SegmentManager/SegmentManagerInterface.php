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

namespace CustomerManagementFrameworkBundle\SegmentManager;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\Element\ElementInterface;

interface SegmentManagerInterface
{
    const CONDITION_AND = 'and';
    const CONDITION_OR = 'or';

    /**
     * Returns a list of customers filtered by segment IDs
     *
     * @param int[] $segmentIds
     * @param string $conditionMode
     *
     * @return \Pimcore\Model\DataObject\Listing\Concrete
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND);

    /**
     * Returns the CustomerSegment with the given ID.
     *
     * @param int $id
     *
     * @return CustomerSegmentInterface
     */
    public function getSegmentById($id);

    /**
     * Returns the CustomerSegmentGroup with the given ID.
     *
     * @param int $id
     *
     * @return CustomerSegmentGroup
     */
    public function getSegmentGroupById($id);

    /**
     * Returns all segments assigned to $element or one of its parents
     * uses the directly assigned segments, not the indexed ones
     *
     * @param ElementInterface $element
     *
     * @return CustomerSegmentInterface[]
     */
    public function getSegmentsForElement(ElementInterface $element): array;

    /**
     * Returns all segments assigned to $id with $type or one of its parents
     * uses the directly assigned segments, not the indexed ones
     *
     * @param string $id
     * @param string $type
     *
     * @return CustomerSegmentInterface[]
     */
    public function getSegmentsForElementId(string $id, string $type): array;

    /**
     * Returns an object list of all customer segments.
     *
     * @return CustomerSegment\Listing
     */
    public function getSegments();

    /**
     * Returns an object list with all customer segment groups.
     *
     * @return CustomerSegmentGroup\Listing
     */
    public function getSegmentGroups();

    /**
     * @param bool $calculated
     *
     * @return \Pimcore\Model\DataObject\Folder
     */
    public function getSegmentsFolder($calculated = true);

    /**
     * @param SegmentBuilderInterface $segmentBuilder
     *
     * @return void
     */
    public function addSegmentBuilder(SegmentBuilderInterface $segmentBuilder);

    /**
     * @return SegmentBuilderInterface[]
     */
    public function getSegmentBuilders();

    /**
     * Could be used to add/remove segments to/from customers.
     * If segments are added or removed this will be tracked in the notes/events tab of the customer.
     * The changes of this method will be persisted when saveMergedSegments() gets called.
     *
     * @param CustomerInterface $customer
     * @param array $addSegments
     * @param array $deleteSegments
     * @param string|null $hintForNotes additional hint for the notes/event entries
     * @param int|true|null $segmentCreatedTimestamp
     *  Optional. Can be used to store the date when the segment was added (for potentially expiring segments).
     *  This feature will only work if you use an object with metadata field to store the segment relations.
     *  Take a look at the docs for more information.
     *  - If true was passed the timestamp will be set to the current time if the segment was added,
     *    otherwise the timestamp will be untouched.
     *  - If a timestamp (int) was passed this timestamp will be stored.
     *    Be careful: if you use this in SegmentBuilders it's not predicatable when/how often the SegmentBuilder will run.
     *    Therefore it's not a good idea to rely on the execution timestamp (time()) here.
     *    It's necessary to do this based on other criterias (for example activities).
     *  - If null was passed the timestamp will be set to null
     * @param int|true|null $segmentApplicationCounter
     *  Optional. Can be used to store a counter how often the segment applies or how often the segment has been added.
     *  This feature will only work if you use an object with metadata field to store the segment relations.
     *  Take a look at the docs for more information.
     *  - If true was passed the counter will increment by one each time the timestamp changes,
     *    otherwise it stay's untouched.
     *  - If a counter (int) was passed this counter will be used as new value.
     *    Be careful: if you use this in SegmentBuilders it's not predicatable when/how often the SegmentBuilder will run.
     *    Therefore it's not a good idea to just always increment it by one here.
     *    It's necessary to do this based on other criterias (for example activities).
     *  - If null was passed the timestamp will be set to null.
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
     * Needs to be called after segments are merged with mergeSegments() in order to persist the segments in the customer object.
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function saveMergedSegments(CustomerInterface $customer);

    /**
     * Create a calculated segment within the given $segmentGroup.
     * The $segmentGroup needs to be either a CustomerSegmentGroup object or a reference string to a calculated CustomerSegmentGroup object.
     *
     * @param string $segmentReference
     * @param string|CustomerSegmentGroup $segmentGroup
     * @param string $segmentName
     * @param string|null $subFolder Optional. If passed a subfolder within the CustomerSegmentGroup will be created.
     *
     * @return CustomerSegmentInterface
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null, $subFolder = null);

    /**
     * Create a customer segment within the given $segmentGroup.
     * The $segmentGroup needs to be either a CustomerSegmentGroup object or a reference string to a CustomerSegmentGroup object.
     *
     * @param string $segmentReference
     * @param string|CustomerSegmentGroup $segmentGroup
     * @param string $segmentName
     * @param bool $calculated
     * @param string|null $subFolder Optional. If passed a subfolder within the CustomerSegmentGroup will be created.
     *
     * @return CustomerSegmentInterface
     */
    public function createSegment(
        $segmentName,
        $segmentGroup,
        $segmentReference = null,
        $calculated = true,
        $subFolder = null
    );

    /**
     * Returns the CustomerSegment with given reference within given CustomerSegmentGroup.
     * If no CustomerSegmentGroup is given it will search globally.
     *
     * @param string $segmentReference
     * @param CustomerSegmentGroup $segmentGroup
     * @param bool $calculated
     *
     * @return CustomerSegmentInterface
     */
    public function getSegmentByReference($segmentReference, CustomerSegmentGroup $segmentGroup = null, $calculated = false);

    /**
     * Creates a segment group.
     *
     * @param string $segmentGroupName
     * @param null $segmentGroupReference
     * @param bool $calculated
     * @param array $values
     *
     * @return CustomerSegmentGroup
     */
    public function createSegmentGroup(
        $segmentGroupName,
        $segmentGroupReference = null,
        $calculated = true,
        array $values = []
    );

    /**
     * Updates a segment group.
     *
     * @param CustomerSegmentGroup $segmentGroup
     * @param array $values
     *
     * @return mixed
     */
    public function updateSegmentGroup(CustomerSegmentGroup $segmentGroup, array $values = []);

    /**
     * Updates a segment.
     *
     * @param CustomerSegmentInterface $segment
     * @param array $values
     *
     * @return mixed
     */
    public function updateSegment(CustomerSegmentInterface $segment, array $values = []);

    /**
     * Returns the SegmentGroup with the given reference.
     *
     * @param $segmentGroupReference
     * @param $calculated
     *
     * @return CustomerSegmentGroup
     */
    public function getSegmentGroupByReference($segmentGroupReference, $calculated);

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param CustomerSegmentInterface[] $ignoreSegments
     *
     * @return CustomerSegmentInterface[]
     */
    public function getSegmentsFromSegmentGroup(CustomerSegmentGroup $segmentGroup, array $ignoreSegments = []);

    /**
     * @param CustomerInterface $customer
     * @param CustomerSegmentInterface $segment
     *
     * @return bool
     */
    public function customerHasSegment(CustomerInterface $customer, CustomerSegmentInterface $segment);

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
     * Return segments of given customers which are within given customer segment group.
     *
     * @param CustomerInterface $customer
     * @param CustomerSegmentGroup|string $group
     *
     * @return CustomerSegmentInterface[]
     */
    public function getCustomersSegmentsFromGroup(CustomerInterface $customer, $group);

    /**
     * Called in pimcore's pre object update hook for CustomerSegment objects.
     *
     * @param CustomerSegmentInterface $segment
     *
     * @return void
     */
    public function preSegmentUpdate(CustomerSegmentInterface $segment);
}
