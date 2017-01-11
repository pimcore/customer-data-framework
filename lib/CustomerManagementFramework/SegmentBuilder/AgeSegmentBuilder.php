<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:25
 */

namespace CustomerManagementFramework\SegmentBuilder;

use CustomerManagementFramework\DataTransformer\Date\TimestampToAge;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Pimcore\Model\Tool\TmpStore;
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
     * @param SegmentManagerInterface $segmentManager
     *
     * @return void
     */
    public function prepare(SegmentManagerInterface $segmentManager)
    {

        $this->segmentGroup = $segmentManager->createSegmentGroup($this->groupName, $this->groupName, true);
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
        $ageSegment = null;

        $getter = 'get' . ucfirst($this->birthDayField);
        if($birthDate = $customer->$getter()) {
            $timestamp = $birthDate->getTimestamp();

            $transformer = new TimestampToAge();
            $age = $transformer->transform($timestamp);

            $this->logger->debug(sprintf("age of customer ID %s: %s years", $customer->getId(), $age));

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

        $segmentManager->mergeSegments($customer, $segments, $segmentManager->getSegmentsFromSegmentGroup($this->segmentGroup, $segments), "AgeSegmentBuilder");
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

    public function maintenance(SegmentManagerInterface $segmentManager)
    {
        $tmpStoreKey = 'plugin_cmf_age_segment_builder';

        if(TmpStore::get($tmpStoreKey)) {
            return;
        }

        $this->logger->debug("execute maintenance of AgeSegmentBuilder");

        TmpStore::add($tmpStoreKey, 1, null, (60*60*24)); // only execute it once per day

        $this->prepare($segmentManager);

        $list = Factory::getInstance()->getCustomerProvider()->getList();
        $list->setCondition("DATE_FORMAT(FROM_UNIXTIME(" . $this->birthDayField ."),'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')");

        $paginator = new \Zend_Paginator($list);
        $paginator->setItemCountPerPage(100);

        $pageCount = $paginator->getPages()->pageCount;
        for($i=1; $i<= $pageCount; $i++) {
            $paginator->setCurrentPageNumber($i);

            foreach($paginator as $customer) {
                $this->calculateSegments($customer, $segmentManager);
            }
        }
    }

}