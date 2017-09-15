<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-12
 * Time: 1:05 PM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\SegmentAssigner;

use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use Pimcore\Model\Element\ElementInterface;

/**
 * Interface SegmentAssignerInterface
 *
 * Interface for assigning segments to objects implementing Pimcore\Model\Element\ElementInterface
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssigner
 */
interface SegmentAssignerInterface {

    /**
     * Assign a segment to an element
     *
     * @param ElementInterface $element
     * @param CustomerSegmentInterface $segment
     * @return bool true on success, false on failure
     */
    public function assign(ElementInterface $element, CustomerSegmentInterface $segment): bool;

    /**
     * Assign a segment to an element using the segment's id
     *
     * @param ElementInterface $element
     * @param string $segmentId
     * @return bool true on success, false on failure
     */
    public function assignById(ElementInterface $element, string $segmentId): bool;

    /**
     * Remove an assigned segment from an element
     *
     * @param ElementInterface $element
     * @param CustomerSegmentInterface $segment
     * @return bool true on success, false on failure
     */
    public function removeAssignment(ElementInterface $element, CustomerSegmentInterface $segment): bool;
}