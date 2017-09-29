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
     * Assigns segments to an element
     *
     * @param ElementInterface $element
     * @param bool $breaksInheritance
     * @param CustomerSegmentInterface[] $segments
     * @return bool true on success, false on failure
     */
    public function assign(ElementInterface $element, bool $breaksInheritance, array $segments): bool;

    /**
     * Assigns segments to an element id using the segments' ids
     *
     * @param string $elementId
     * @param string $type
     * @param bool $breaksInheritance
     * @param string[] $segmentIds
     * @return bool true on success, false on failure
     */
    public function assignById(string $elementId, string $type, bool $breaksInheritance, array $segmentIds): bool;

    /**
     * removes all references to the given element from assignment, queue and index tables
     *
     * @param string $elementId
     * @param string $type
     * @return bool
     */
    public function removeElementById(string $elementId, string $type): bool;

    /**
     * adds an element's children to the segment assignment queue
     *
     * @param string $elementId
     * @param string $type
     * @return bool
     */
    public function enqueueChildren(string $elementId, string $type): bool;
}