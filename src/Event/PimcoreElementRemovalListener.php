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

    /**
     * @param SegmentAssignerInterface $segmentAssigner
     * @param TypeMapperInterface $typeMapper
     */
    public function __construct(SegmentAssignerInterface $segmentAssigner, TypeMapperInterface $typeMapper)
    {
        $this->setSegmentAssigner($segmentAssigner);
        $this->setTypeMapper($typeMapper);
    }

    /**
     * @return SegmentAssignerInterface
     */
    public function getSegmentAssigner(): SegmentAssignerInterface
    {
        return $this->segmentAssigner;
    }

    /**
     * @param SegmentAssignerInterface $segmentAssigner
     */
    public function setSegmentAssigner(SegmentAssignerInterface $segmentAssigner)
    {
        $this->segmentAssigner = $segmentAssigner;
    }

    /**
     * @return TypeMapperInterface
     */
    public function getTypeMapper(): TypeMapperInterface
    {
        return $this->typeMapper;
    }

    /**
     * @param TypeMapperInterface $typeMapper
     */
    public function setTypeMapper(TypeMapperInterface $typeMapper)
    {
        $this->typeMapper = $typeMapper;
    }

    /**
     * @inheritdoc
     */
    public function onPostDelete(ElementEventInterface $event)
    {
        $id = $event->getElement()->getId();
        $type = $this->getTypeMapper()->getTypeStringByObject($event->getElement());

        $this->getSegmentAssigner()->removeElementById((string) $id, $type);
    }
}
