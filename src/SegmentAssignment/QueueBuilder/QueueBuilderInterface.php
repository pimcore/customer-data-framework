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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder;

/**
 * Interface for placing elements into the segment assignment queue
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssignment\QueueBuilder
 */
interface QueueBuilderInterface
{
    /**
     * adds a single element to the segment assignment queue
     *
     *
     */
    public function enqueue(string $elementId, string $type): bool;

    /**
     * adds an element's children to the segment assignment queue
     *
     *
     */
    public function enqueueChildren(string $elementId, string $type): bool;
}
