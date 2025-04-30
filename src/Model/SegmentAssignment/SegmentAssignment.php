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

namespace CustomerManagementFrameworkBundle\Model\SegmentAssignment;

use CustomerManagementFrameworkBundle\Model\SegmentAssignmentInterface;

class SegmentAssignment implements SegmentAssignmentInterface
{
    /**
     * @var string[]
     */
    private $segmentIds = [];

    /**
     * @var array
     */
    private $segments = [];

    /**
     * @var string
     */
    private $elementId = '';

    /**
     * @var string
     */
    private $elementType = '';

    /**
     * @var bool
     */
    private $breaksInheritance = false;

    public function getSegmentIds(): array
    {
        return $this->segmentIds;
    }

    public function getSegments(): array
    {
        if ([] === $this->segments) {
            //load segments
        }

        return $this->segments;
    }

    public function getElementId(): string
    {
        return $this->elementId;
    }

    public function getElementType(): string
    {
        return $this->elementType;
    }

    public function breaksInheritance(): bool
    {
        return $this->breaksInheritance;
    }

    public function setSegmentIds(array $segmentIds): SegmentAssignmentInterface
    {
        $this->segmentIds = $segmentIds;

        return $this;
    }

    public function setSegments(array $segments): SegmentAssignmentInterface
    {
        $this->segments = $segments;

        return $this;
    }

    public function setElementId(string $id): SegmentAssignmentInterface
    {
        $this->elementId = $id;

        return $this;
    }

    public function setElementType(string $type): SegmentAssignmentInterface
    {
        $this->elementType = $type;

        return $this;
    }

    public function setBreaksInheritance(bool $breaks): SegmentAssignmentInterface
    {
        $this->breaksInheritance = $breaks;

        return $this;
    }

    /**
     * SegmentAssignment constructor.
     *
     * @param string[] $segmentIds
     */
    public function __construct(array $segmentIds = [], int $elementId = 0, string $elementType = '', bool $breaksInheritance = false)
    {
        $this->setSegmentIds($segmentIds)->setElementId((string) $elementId)->setElementType($elementType)->setBreaksInheritance($breaksInheritance);
    }
}
