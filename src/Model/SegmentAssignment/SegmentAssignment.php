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

    /**
     * @inheritDoc
     */
    public function getSegmentIds(): array
    {
        return $this->segmentIds;
    }

    /**
     * @inheritDoc
     */
    public function getSegments(): array
    {
        if ([] === $this->segments) {
            //load segments
        }

        return $this->segments;
    }

    /**
     * @inheritDoc
     */
    public function getElementId(): string
    {
        return $this->elementId;
    }

    /**
     * @inheritDoc
     */
    public function getElementType(): string
    {
        return $this->elementType;
    }

    /**
     * @inheritDoc
     */
    public function breaksInheritance(): bool
    {
        return $this->breaksInheritance;
    }

    /**
     * @inheritDoc
     */
    public function setSegmentIds(array $segmentIds): SegmentAssignmentInterface
    {
        $this->segmentIds = $segmentIds;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSegments(array $segments): SegmentAssignmentInterface
    {
        $this->segments = $segments;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setElementId(string $id): SegmentAssignmentInterface
    {
        $this->elementId = $id;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setElementType(string $type): SegmentAssignmentInterface
    {
        $this->elementType = $type;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBreaksInheritance(bool $breaks): SegmentAssignmentInterface
    {
        $this->breaksInheritance = $breaks;

        return $this;
    }

    /**
     * SegmentAssignment constructor.
     *
     * @param string[] $segmentIds
     * @param int $elementId
     * @param string $elementType
     * @param bool $breaksInheritance
     */
    public function __construct(array $segmentIds = [], int $elementId = 0, string $elementType = '', bool $breaksInheritance = false)
    {
        $this->setSegmentIds($segmentIds)->setElementId((string) $elementId)->setElementType($elementType)->setBreaksInheritance($breaksInheritance);
    }
}
