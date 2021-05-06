<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
     * @param string $elementId
     * @param string $type
     *
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
