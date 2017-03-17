<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:00
 */

namespace CustomerManagementFramework\SegmentManager;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use Pimcore\Model\Object\CustomerSegment;
use Pimcore\Model\Object\CustomerSegmentGroup;
use Psr\Log\LoggerInterface;

interface SegmentManagerInterface {

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
     * @return CustomerSegmentInterface
     */
    public function getSegmentById($id);

    /**
     * Returns the CustomerSegmentGroup with the given ID.
     * 
     * @param int $id
     * @return CustomerSegmentGroup
     */
    public function getSegmentGroupById($id);

    /**
     * Returns an array with all customer segments. Optionally this could be filtered by given params.
     * 
     * @param array $params
     *
     * @return CustomerSegment[]
     */
    public function getSegments(array $params = []);

    /**
     * Returns an array with all customer segment groups. Optionally this could be filtered by given params.
     * 
     * @param array $params
     *
     * @return CustomerSegment[]
     */
    public function getSegmentGroups(array $params = []);

    /**
     * Applies all SegmentBuilders to customers. If the param $changesQueue only is set to true this is done only for customers which where changed since the last run. 
     * If $segmentBuilderClass is given (php class name) then only this SegmentBuilder will be executed.
     *
     * @param bool $changesQueueOnly
     * @param string $segmentBuilderClass
     * 
     * @return void
     */
    public function buildCalculatedSegments($changesQueueOnly = true, $segmentBuilderClass = null);

    /**
     * Calls all maintenance methods of all SegmentBuilders
     * 
     * @return void
     */
    public function executeSegmentBuilderMaintenance();

    /**
     * Could be used to add/remove segments to/from customers. If segments are added or removed this will be tracked in the notes/events tab of the customer. With the optional $hintForNotes parameter it's possible to add an iditional hint to the notes/event entries.
     * 
     * @param CustomerInterface $customer
     * @param array             $addSegments
     * @param array             $deleteSegments
     * @param string|null       $hintForNotes
     *
     * @return mixed
     */
    public function mergeSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = [], $hintForNotes = null);


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
    public function createSegment($segmentName, $segmentGroup, $segmentReference = null, $calculated = true, $subFolder = null);

    /**
     * Returns the CustomerSegment with given reference within given CustomerSegmentGroup.
     *
     * @param string $segmentReference
     * @param CustomerSegmentGroup $segmentGroup
     * @param bool $calculated
     *
     * @return CustomerSegmentInterface
     */
    public function getSegmentByReference($segmentReference, CustomerSegmentGroup $segmentGroup, $calculated = false);

    /**
     * Creates a segment group.
     * 
     * @param       $segmentGroupName
     * @param null  $segmentGroupReference
     * @param bool  $calculated
     * @param array $values
     *
     * @return CustomerSegmentGroup
     */
    public function createSegmentGroup($segmentGroupName, $segmentGroupReference = null, $calculated = true, array $values = []);

    /**
     * Updates a segment group.
     * 
     * @param CustomerSegmentGroup $segmentGroup
     * @param array                $values
     *
     * @return mixed
     */
    public function updateSegmentGroup(CustomerSegmentGroup $segmentGroup, array $values = []);

    /**
     * Updates a sgement.
     * 
     * @param CustomerSegment $segment
     * @param array           $values
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
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer);

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
     * @return void
     */
    public function addCustomerToChangesQueue(CustomerInterface $customer);

    /**
     * Called in pimcore's pre object update hook for CustomerSegment objects.
     *
     * @param CustomerSegmentInterface $segment
     *
     * @return void
     */
    public function preSegmentUpdate(CustomerSegmentInterface $segment);


}
