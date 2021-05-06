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

namespace CustomerManagementFrameworkBundle\Model;

interface SegmentAssignmentInterface
{
    /**
     * returns assigned segments' ids
     *
     * @return string[]
     */
    public function getSegmentIds(): array;

    /**
     * returns assigned Segments     *
     *
     * @return array
     */
    public function getSegments(): array;

    /**
     * returns the affiliated element's id
     *
     * @return string
     */
    public function getElementId(): string;

    /**
     * returns the affiliated element's type
     *
     * @return string
     */
    public function getElementType(): string;

    /**
     * returns whether the affiliated element breaks inheritance
     *
     * @return bool
     */
    public function breaksInheritance(): bool;

    /**
     * @param string[] $segmentIds
     *
     * @return SegmentAssignmentInterface
     */
    public function setSegmentIds(array $segmentIds): self;

    /**
     * @param SegmentAssignmentInterface[] $segments
     *
     * @return SegmentAssignmentInterface
     */
    public function setSegments(array $segments): self;

    /**
     * @param string $id
     *
     * @return SegmentAssignmentInterface
     */
    public function setElementId(string $id): self;

    /**
     * @param string $type
     *
     * @return SegmentAssignmentInterface
     */
    public function setElementType(string $type): self;

    /**
     * @param bool $breaks
     *
     * @return SegmentAssignmentInterface
     */
    public function setBreaksInheritance(bool $breaks): self;
}
