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

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\SegmentAssignment\SegmentAssigner\SegmentAssignerInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use Pimcore\Event\Model\ElementEventInterface;

/**
 * Class PimcoreElementRemovalListener
 *
 * @package CustomerManagementFrameworkBundle\Event
 */
class PimcoreElementRemovalListener implements PimcoreElementRemovalListenerInterface
{
    /**
     * @var SegmentAssignerInterface
     */
    private $segmentAssigner = null;

    /**
     * @var TypeMapperInterface
     */
    private $typeMapper = null;

    public function __construct(SegmentAssignerInterface $segmentAssigner, TypeMapperInterface $typeMapper)
    {
        $this->setSegmentAssigner($segmentAssigner);
        $this->setTypeMapper($typeMapper);
    }

    public function getSegmentAssigner(): SegmentAssignerInterface
    {
        return $this->segmentAssigner;
    }

    public function setSegmentAssigner(SegmentAssignerInterface $segmentAssigner)
    {
        $this->segmentAssigner = $segmentAssigner;
    }

    public function getTypeMapper(): TypeMapperInterface
    {
        return $this->typeMapper;
    }

    public function setTypeMapper(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    public function onPostDelete(ElementEventInterface $event)
    {
        $id = $event->getElement()->getId();
        $type = $this->getTypeMapper()->getTypeStringByObject($event->getElement());

        $this->getSegmentAssigner()->removeElementById((string) $id, $type);
    }
}
