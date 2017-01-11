<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:25
 */

namespace CustomerManagementFramework\SegmentBuilder;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

class GenderSegmentBuilder extends AbstractSegmentBuilder {

    const MALE = 'male';
    const FEMALE = 'female';
    const NOT_SET = 'not-set';

    private $config;
    private $logger;

    private $maleSegment;
    private $femaleSegment;
    private $notsetSegment;
    private $segmentGroup;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * prepare data and configurations which could be reused for all calculateSegments() calls
     *
     * @param SegmentManagerInterface $segmentManager
     *
     * @return void
     */
    public function prepare(SegmentManagerInterface $segmentManager)
    {
        $segmentGroupName = $this->config->segmentGroup ? : 'Gender';

        $this->maleSegment = $segmentManager->createCalculatedSegment((string)$this->config->maleSegmentName ? : self::MALE, $segmentGroupName);
        $this->femaleSegment = $segmentManager->createCalculatedSegment((string)$this->config->femaleSegmentName ? : self::FEMALE, $segmentGroupName);
        $this->notsetSegment = $segmentManager->createCalculatedSegment((string)$this->config->notsetSegmentName ? : self::NOT_SET, $segmentGroupName);

        $this->segmentGroup = $this->maleSegment->getGroup();
    }

    /**
     * build segment(s) for given customer
     *
     * @param CustomerInterface $customer
     * @param SegmentManagerInterface $segmentManager
     *
     * @return void
     */
    public function calculateSegments(CustomerInterface $customer, SegmentManagerInterface $segmentManager)
    {
        $valueMapping = $this->config->valueMapping->toArray();
        $gender = $valueMapping[$customer->getGender()] ? : self::NOT_SET;

        if($gender == self::MALE) {
            $segment = $this->maleSegment;
        }elseif($gender == self::FEMALE) {
            $segment = $this->femaleSegment;
        } else {
            $segment = $this->notsetSegment;
        }

        $segmentManager->mergeSegments($customer, [$segment], $segmentManager->getSegmentsFromSegmentGroup($this->segmentGroup,[$segment]), "GenderSegmentBuilder");
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return "GenderSegmentBuilder";
    }

    public function executeOnCustomerSave()
    {
        return true;
    }


}