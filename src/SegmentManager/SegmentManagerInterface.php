<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\SegmentManager;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentBuilder\SegmentBuilderInterface;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;

interface SegmentManagerInterface
{
    const CONDITION_AND = 'and';
    const CONDITION_OR = 'or';

    /**
     * Returns a list of customers which are within the given customer segments.
     *
     * @param int[] $segmentIds
     * @param string $conditionMode
     *
     * @return CustomerSegment\Listing
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
     * Returns an array with all customer segments.
     *
     * @param array $params
     *
     * @return CustomerSegment\Listing
     */
    public function getSegments();

    /**
     * Returns an object list with all customer segment groups. Optionally this could be filtered by given params.
     *
     * @param array $params
     *
     * @return CustomerSegmentGroup\Listing
     */
    public function getSegmentGroups();

    /**
     * @param bool $calculated
     *
     * @return \Pimcore\Model\Object\Folder
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

    /**
     * Create a calculated segment within the given $segmentGroup. The $segmentGroup needs to be either a CustomerSegmentGroup object or a reference to a calculated CustomerSegmentGroup object.
     * With the (optional) $subFolder parameter it's possible to create subfolders within the CustomerSegmentGroup for better a better overview.
     *
     * @param string $segmentReference
     * @param string|CustomerSegmentGroup $segmentGroup
     * @param string $segmentName
     * @param string $subFolder
     *
     * @return CustomerSegmentInterface
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null, $subFolder = null);

    /**
     * * Create a customer segment within the given $segmentGroup. The $segmentGroup needs to be either a CustomerSegmentGroup object or a reference to a CustomerSegmentGroup object.
     * With the (optional) $subFolder parameter it's possible to create subfolders within the CustomerSegmentGroup for better a better overview.
     *
     * @param string $segmentReference
     * @param string|CustomerSegmentGroup $segmentGroup
     * @param string $segmentName
     * @param bool $calculated
     * @param string $subFolder
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
     * Returns the CustomerSegment with given reference within given CustomerSegmentGroup. If no CustomerSegmentGroup is given it will search globally.
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
     * @param       $segmentGroupName
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
     * Updates a sgement.
     *
     * @param CustomerSegment $segment
     * @param array $values
     *
     * @return mixed
     */
    public function updateSegment(CustomerSegment $segment, array $values = []);

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
