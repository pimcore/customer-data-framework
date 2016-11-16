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
     * @param int[] $segmentIds
     *
     * @return CustomerSegment\Listing
     */
    public function getCustomersBySegmentIds(array $segmentIds, $conditionMode = self::CONDITION_AND);

    /**
     * @param int $segmentId
     *
     * @return CustomerSegment
     */
    public function getSegmentById($segmentId);

    /**
     * @param array $params
     *
     * @return CustomerSegment[]
     */
    public function getSegments(array $params);

    /**
     * @return void
     */
    public function buildCalculatedSegments(LoggerInterface $logger);

    /**
     * @param CustomerInterface          $customer
     * @param CustomerSegmentInterface[] $addSegments
     * @param CustomerSegmentInterface[] $deleteSegments
     *
     * @return void
     */
    public function mergeCalculatedSegments(CustomerInterface $customer, array $addSegments, array $deleteSegments = []);

    /**
     * @param $segmentReference
     *
     * @return CustomerSegmentInterface
     */
    public function createCalculatedSegment($segmentReference, $segmentGroup, $segmentName = null);


    /**
     * @param CustomerSegmentGroup $segmentGroup
     * @param array                $ignoreSegments
     *
     * @return mixed
     */
    public function getSegmentsFromSegmentGroup(CustomerSegmentGroup $segmentGroup, array $ignoreSegments = []);


}