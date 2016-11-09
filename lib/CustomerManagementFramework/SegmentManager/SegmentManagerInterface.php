<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 12:00
 */

namespace CustomerManagementFramework\SegmentManager;

use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Model\Object\CustomerSegment;

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
}