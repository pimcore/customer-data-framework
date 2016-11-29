<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 22.11.2016
 * Time: 13:16
 */

namespace CustomerManagementFramework\ActionTrigger\Action;

use CustomerManagementFramework\ActionTrigger\Trigger\ActionDefinitionInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use Pimcore\Model\Object\CustomerSegment;

class AddSegment extends AbstractAction {

    const OPTION_SEGMENT_ID = 'segmentId';

    public function process(ActionDefinitionInterface $actionDefinition, CustomerInterface $customer)
    {

        $options = $actionDefinition->getOptions();

        if(empty($options[self::OPTION_SEGMENT_ID])) {
            $this->logger->error("AddSegment action: segmentId option not set");
        }


        if($segment = CustomerSegment::getById(intval($options[self::OPTION_SEGMENT_ID]))) {

            $this->logger->debug(sprintf("AddSegment action: add segment %s to customer %s", $segment->getId(), $customer->getId()));

            if($segment->getCalculated()) {
                Factory::getInstance()->getSegmentManager()->mergeCalculatedSegments($customer, [$segment]);
            } else {
                Factory::getInstance()->getSegmentManager()->mergeManualSegments($customer, [$segment]);
            }

        } else {
            $this->logger->error(sprintf("AddSegment action: segment with ID %s not found", $options[self::OPTION_SEGMENT_ID]));
        }
    }
}