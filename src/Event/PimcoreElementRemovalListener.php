<?php
/**
 * @var \Pimcore\Templating\PhpEngine $this
 * @var \Pimcore\Templating\PhpEngine $view
 * @var \Pimcore\Templating\GlobalVariables $app
 */

namespace CustomerManagementFrameworkBundle\Event;

use CustomerManagementFrameworkBundle\SegmentAssignment\SegmentAssigner\SegmentAssignerInterface;
use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;
use Pimcore\Event\Model\ElementEventInterface;

/**
 * Class PimcoreElementRemovalListener
 * @package CustomerManagementFrameworkBundle\Event
 */
class PimcoreElementRemovalListener implements PimcoreElementRemovalListenerInterface {

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
    public function __construct(SegmentAssignerInterface $segmentAssigner, TypeMapperInterface $typeMapper) {
        $this->setSegmentAssigner($segmentAssigner);
        $this->setTypeMapper($typeMapper);
    }

    /**
     * @return SegmentAssignerInterface
     */
    public function getSegmentAssigner(): SegmentAssignerInterface {
        return $this->segmentAssigner;
    }

    /**
     * @param SegmentAssignerInterface $segmentAssigner
     */
    public function setSegmentAssigner(SegmentAssignerInterface $segmentAssigner) {
        $this->segmentAssigner = $segmentAssigner;
    }

    /**
     * @return TypeMapperInterface
     */
    public function getTypeMapper(): TypeMapperInterface {
        return $this->typeMapper;
    }

    /**
     * @param TypeMapperInterface $typeMapper
     */
    public function setTypeMapper(TypeMapperInterface $typeMapper) {
        $this->typeMapper = $typeMapper;
    }

    /**
     * @inheritdoc
     */
    public function onPostDelete(ElementEventInterface $event){
        $id = $event->getElement()->getId();
        $type = $this->getTypeMapper()->getTypeStringByObject($event->getElement());

        $this->getSegmentAssigner()->removeElementById($id, $type);
    }

}
