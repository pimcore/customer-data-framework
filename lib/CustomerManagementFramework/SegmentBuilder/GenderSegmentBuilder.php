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
use Pimcore\Model\Object\CustomerSegment;
use Psr\Log\LoggerInterface;

class GenderSegmentBuilder implements SegmentBuilderInterface {

    const MALE = 'male';
    const FEMALE = 'female';

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
     * @return \Pimcore\Model\Object\Customer\Listing
     */
    public function prepare(SegmentManagerInterface $segmentManager)
    {
        $this->maleSegment = $segmentManager->createCalculatedSegment('male','gender');
        $this->femaleSegment = $segmentManager->createCalculatedSegment('female','gender');
        $this->notsetSegment = $segmentManager->createCalculatedSegment('not-set','gender');

        $this->segmentGroup = $this->maleSegment->getGroup();
    }

    /**
     * build segment(s) for given customer
     *
     * @param CustomerInterface $customer
     *
     * @return CustomerSegmentInterface[]
     */
    public function calculateSegments(CustomerInterface $customer, SegmentManagerInterface $segmentManager)
    {

        if($customer->getGender() == self::MALE) {
            $segment = $this->maleSegment;
        }elseif($customer->getGender() == self::FEMALE) {
            $segment = $this->femaleSegment;
        } else {
            $segment = $this->notsetSegment;
        }

        $segmentManager->mergeCalculatedSegments($customer, [$segment], $segmentManager->getSegmentsFromSegmentGroup($this->segmentGroup,[$segment]));
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