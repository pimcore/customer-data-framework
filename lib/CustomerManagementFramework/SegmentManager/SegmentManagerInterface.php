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
     * @param int[] $segmentIds
     *
     * @return CustomerSegment\Listing
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND);


    /**
     * @param array $params
     *
     * @return CustomerSegment[]
     */
    public function getSegments(array $params);

    /**
     * @param array $params
     *
     * @return CustomerSegment[]
     */
    public function getSegmentGroups(array $params);

    /**
     * @return void
     */
    public function buildCalculatedSegments($changesQueueOnly = true);

    /**
     * @return void
     */
    public function executeSegmentBuilderMaintenance();

    /**
     * @param CustomerInterface          $customer
     * @param CustomerSegmentInterface[] $addSegments
     * @param CustomerSegmentInterface[] $deleteSegments
     *
     * @return void
     */
    public function mergeCalculatedSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = []);

    /**
     * @param CustomerInterface          $customer
     * @param CustomerSegmentInterface[] $addSegments
     * @param CustomerSegmentInterface[] $deleteSegments
     *
     * @return void
     */
    public function mergeManualSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = []);

    /**
     * @param $segmentReference
     *
     * @return CustomerSegmentInterface
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null);

    /**
     * @param      $segmentGroupName
     * @param null $segmentGroupReference
     * @param bool $calculated
     *
     * @return CustomerSegmentGroup
     */
    public function createSegmentGroup($segmentGroupName, $segmentGroupReference = null, $calculated = false);

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
     *
     * @return void
     */
    public function addCustomerToChangesQueue(CustomerInterface $customer);

    public function preSegmentUpdate(CustomerSegmentInterface $segment);

    public function customerHasSegment(CustomerInterface $customer, CustomerSegmentInterface $segment);


}
