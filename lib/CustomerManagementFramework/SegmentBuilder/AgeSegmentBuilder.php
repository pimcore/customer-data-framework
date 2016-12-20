<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:25
 */

namespace CustomerManagementFramework\SegmentBuilder;

use Carbon\Carbon;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

class AgeSegmentBuilder extends AbstractSegmentBuilder {

    private $config;
    private $logger;

    private $groupName;
    private $segmentGroup;
    private $ageGroups;
    private $birthDayField;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;

        $this->logger = $logger;

        $this->groupName = (string)$config->segmentGroup ? : 'Age';

        $this->ageGroups = $config->ageGroups ? : [[0,10],[11,15],[16,18],[18,25],[26,30],[31,40],[41,50],[51,60],[61,70],[71,80],[81,120]];

        $this->birthDayField = (string)$config->birthDayField ? : 'birthday';
    }

    /**
     * prepare data and configurations which could be reused for all calculateSegments() calls
     *
     * @return \Pimcore\Model\Object\Customer\Listing
     */
    public function prepare(SegmentManagerInterface $segmentManager)
    {

        $this->segmentGroup = $segmentManager->createSegmentGroup($this->groupName, $this->groupName, true);
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
        $ageSegment = null;

        $getter = 'get' . ucfirst($this->birthDayField);
        if($birthDate = $customer->$getter()) {
            $timestamp = $birthDate->getTimestamp();

            $date = Carbon::createFromTimestamp($timestamp);
            $today = new Carbon();
            $age = $today->diffInYears($date);

           // $this->logger->alert("age: $age");

            foreach($this->ageGroups as $ageGroup) {
                $from = $ageGroup[0];
                $to = $ageGroup[1];

                if($age >= $from && $age <= $to) {
                    $ageSegment = $segmentManager->createCalculatedSegment($from . ' - ' . $to, $this->groupName);
                }
            }
        }

        $segments = [];
        if($ageSegment) {
            $segments[] = $ageSegment;
        }

        $segmentManager->mergeCalculatedSegments($customer, $segments, $segmentManager->getSegmentsFromSegmentGroup($this->segmentGroup, $segments));
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return "AgeSegmentBuilder";
    }

    public function executeOnCustomerSave()
    {
        return true;
    }


}