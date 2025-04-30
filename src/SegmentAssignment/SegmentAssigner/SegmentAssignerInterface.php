<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
interface SegmentAssignerInterface
{
    /**
     * Assigns segments to an element
     *
     * @param CustomerSegmentInterface[]|int[] $segments
     *
     * @return bool true on success, false on failure
     */
    public function assign(ElementInterface $element, bool $breaksInheritance, array $segments): bool;

    /**
     * Assigns segments to an element id using the segments' ids
     *
     * @param string[] $segmentIds
     *
     * @return bool true on success, false on failure
     */
    public function assignById(string $elementId, string $type, bool $breaksInheritance, array $segmentIds): bool;

    /**
     * removes all references to the given element from assignment, queue and index tables
     *
     *
     */
    public function removeElementById(string $elementId, string $type): bool;
}
