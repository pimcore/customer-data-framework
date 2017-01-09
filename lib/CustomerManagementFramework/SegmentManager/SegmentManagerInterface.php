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

    public function __construct(LoggerInterface $logger);

    /**
     * Returns a list of customers which are within the given customer segments.
     * 
     * @param int[] $segmentIds
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
     * @return void
     */
    public function buildCalculatedSegments($changesQueueOnly = true);

    /**
     * @return void
     */
    public function executeSegmentBuilderMaintenance();

    /**
     * @param CustomerInterface $customer
     * @param array             $addSegments
     * @param array             $deleteSegments
     * @param string|null       $hintForNotes
     *
     * @return mixed
     */
    public function mergeSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = [], $hintForNotes = null);


    /**
     * @param $segmentReference
     *
     * @return CustomerSegmentInterface
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null, $subFolder = null);

    /**
     * @param      $segmentReference
     * @param      $segmentGroup
     * @param null $segmentName
     * @param bool $calculated
     * @param null $subFolder
     *
     * @return CustomerSegmentInterface
     */
    public function createSegment($segmentName, $segmentGroup, $segmentReference = null, $calculated = true, $subFolder = null);

    /**
     * @param $segmentReference
     * @param CustomerSegmentGroup $segmentGroup
     *
     * @return CustomerSegmentInterface
     */
    public function getSegmentByReference($segmentReference, CustomerSegmentGroup $segmentGroup, $calculated = false);

    /**
     * @param      $segmentGroupName
     * @param null $segmentGroupReference
     * @param bool $calculated
     *
     * @return CustomerSegmentGroup
     */
    public function createSegmentGroup($segmentGroupName, $segmentGroupReference = null, $calculated = false, array $values = []);

    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array                $values
     *
     * @return mixed
     */
    public function updateSegmentGroup(CustomerSegmentGroup $segmentGroup, array $values = []);

    /**
     * @param CustomerSegment $segment
     * @param array           $values
     *
     * @return mixed
     */
    public function updateSegment(CustomerSegment $segment, array $values = []);

    /**
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
     * /
    public function customerHasSegment(CustomerInterface $customer, CustomerSegmentInterface $segment);

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function addCustomerToChangesQueue(CustomerInterface $customer);

    public function preSegmentUpdate(CustomerSegmentInterface $segment);


}
