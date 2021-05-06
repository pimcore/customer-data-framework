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

namespace CustomerManagementFrameworkBundle\SegmentBuilder;

use CustomerManagementFrameworkBundle\DataTransformer\Date\TimestampToAge;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\Tool\TmpStore;

class AgeSegmentBuilder extends AbstractSegmentBuilder
{
    use LoggerAware;

    private $groupName;
    private $segmentGroup;
    private $ageGroups;
    private $birthDayField;

    public function __construct($groupName = 'Age', $ageGroups = [], $birthDayField = 'birthDate')
    {
        $this->groupName = $groupName;

        $this->ageGroups = sizeof($ageGroups) ? $ageGroups : [
            [0, 10],
            [11, 15],
            [16, 18],
            [18, 25],
            [26, 30],
            [31, 40],
            [41, 50],
            [51, 60],
            [61, 70],
            [71, 80],
            [81, 120],
        ];

        $this->birthDayField = $birthDayField;
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

        $getter = 'get'.ucfirst($this->birthDayField);
        if ($birthDate = $customer->$getter()) {
            $timestamp = $birthDate->getTimestamp();

            $transformer = new TimestampToAge();
            $age = $transformer->transform($timestamp, []);

            $this->getLogger()->debug(sprintf('age of customer ID %s: %s years', $customer->getId(), $age));

            foreach ($this->ageGroups as $ageGroup) {
                $from = $ageGroup[0];
                $to = $ageGroup[1];

                if ($age >= $from && $age <= $to) {
                    $ageSegment = $segmentManager->createCalculatedSegment($from.' - '.$to, $this->groupName);
                }
            }
        }

        $segments = [];
        if ($ageSegment) {
            $segments[] = $ageSegment;
        }

        $segmentManager->mergeSegments(
            $customer,
            $segments,
            $segmentManager->getSegmentsFromSegmentGroup($this->segmentGroup, $segments),
            'AgeSegmentBuilder'
        );
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return 'AgeSegmentBuilder';
    }

    public function executeOnCustomerSave()
    {
        return true;
    }

    public function maintenance(SegmentManagerInterface $segmentManager)
    {
        $tmpStoreKey = 'plugin_cmf_age_segment_builder';

        if (TmpStore::get($tmpStoreKey)) {
            return;
        }

        $this->getLogger()->info('execute maintenance of AgeSegmentBuilder');

        TmpStore::add($tmpStoreKey, 1, null, (60 * 60 * 24)); // only execute it once per day

        $this->prepare($segmentManager);

        $list = \Pimcore::getContainer()->get('cmf.customer_provider')->getList();
        $list->setCondition(
            'DATE_FORMAT(FROM_UNIXTIME('.$this->birthDayField."),'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')"
        );

        $paginator = $this->paginator->paginate($list, 1, 100);

        $pageCount = $paginator->getPaginationData()['pageCount'];
        for ($i = 1; $i <= $pageCount; $i++) {
            $paginator = $this->paginator->paginate($list, $i, 100);

            foreach ($paginator as $customer) {
                $this->calculateSegments($customer, $segmentManager);
                $segmentManager->saveMergedSegments($customer);
            }
        }
    }
}
