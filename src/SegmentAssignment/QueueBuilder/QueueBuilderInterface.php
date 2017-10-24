<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 23.10.2017
 * Time: 16:43
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder;

/**
 * Interface for placing elements into the segment assignment queue
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder
 */
interface QueueBuilderInterface {

    /**
     * adds a single element to the segment assignment queue
     *
     * @param string $elementId
     * @param string $type
     * @return bool
     */
    public function enqueue(string $elementId, string $type): bool;

    /**
     * adds an element's children to the segment assignment queue
     *
     * @param string $elementId
     * @param string $type
     *
     * @return bool
     */
    public function enqueueChildren(string $elementId, string $type): bool;
}