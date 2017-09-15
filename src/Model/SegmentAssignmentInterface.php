<?php
/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model;

interface SegmentAssignmentInterface {

    /**
     * returns assigned segments' ids
     * @return string[]
     */
    public function getSegmentIds(): array;

    /**
     * returns assigned Segments     *
     * @return array
     */
    public function getSegments(): array;

    /**
     * returns the affiliated element's id
     * @return string
     */
    public function getElementId(): string;

    /**
     * returns the affiliated element's type
     * @return string
     */
    public function getElementType(): string;

    /**
     * returns whether the affiliated element breaks inheritance
     * @return bool
     */
    public function breaksInheritance(): bool;

    /**
     * @param string[] $segmentIds
     * @return SegmentAssignmentInterface
     */
    public function setSegmentIds(array $segmentIds): SegmentAssignmentInterface;

    /**
     * @param SegmentAssignmentInterface[] $segments
     * @return SegmentAssignmentInterface
     */
    public function setSegments(array $segments): SegmentAssignmentInterface;

    /**
     * @param string $id
     * @return SegmentAssignmentInterface
     */
    public function setElementId(string $id): SegmentAssignmentInterface;

    /**
     * @param string $type
     * @return SegmentAssignmentInterface
     */
    public function setElementType(string $type): SegmentAssignmentInterface;

    /**
     * @param bool $breaks
     * @return SegmentAssignmentInterface
     */
    public function setBreaksInheritance(bool $breaks): SegmentAssignmentInterface;
}