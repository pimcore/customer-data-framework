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

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;
use CustomerManagementFrameworkBundle\Factory;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;

class StateSegmentBuilder extends AbstractSegmentBuilder
{
    private $countryTransformers;
    private $groupName;
    private $segmentGroup;

    public function __construct($groupName = 'State', array $countryTransformers = [])
    {
        $this->countryTransformers = sizeof($countryTransformers) ? $countryTransformers : [
            'AT' => \CustomerManagementFrameworkBundle\DataTransformer\Zip2State\At::class,
            'DE' => \CustomerManagementFrameworkBundle\DataTransformer\Zip2State\De::class,
            'CH' => \CustomerManagementFrameworkBundle\DataTransformer\Zip2State\Ch::class,
        ];

        $this->groupName = $groupName ?: 'State';
    }

    /**
     * prepare data and configurations which could be reused for all calculateSegments() calls
     *
     * @param SegmentManagerInterface $segmentManager
     */
    public function prepare(SegmentManagerInterface $segmentManager)
    {
        $this->segmentGroup = $segmentManager->createSegmentGroup($this->groupName, $this->groupName, true);

        foreach ($this->countryTransformers as $key => $transformer) {
            if (is_object($transformer)) {
                continue;
            }
            $transformer = Factory::getInstance()->createObject((string) $transformer, DataTransformerInterface::class);
            $this->countryTransformers[$key] = $transformer;
        }
    }

    /**
     * build segment(s) for given customer
     *
     * @param CustomerInterface $customer
     * @param SegmentManagerInterface $segmentManager
     */
    public function calculateSegments(CustomerInterface $customer, SegmentManagerInterface $segmentManager)
    {
        $countryCode = $customer->getCountryCode();

        $stateSegment = null;

        if (isset($this->countryTransformers[$countryCode])) {
            $transformer = $this->countryTransformers[$countryCode];

            if ($state = $transformer->transform($customer->getZip())) {
                $stateSegment = $segmentManager->createCalculatedSegment(
                    $countryCode.' - '.$state,
                    $this->groupName,
                    $countryCode.' - '.$state,
                    $countryCode
                );
            }
        }

        $segments = [];
        if ($stateSegment) {
            $segments[] = $stateSegment;
        }

        $segmentManager->mergeSegments(
            $customer,
            $segments,
            $segmentManager->getSegmentsFromSegmentGroup($this->segmentGroup, $segments),
            'StateSegmentBuilder'
        );
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return 'StateSegmentBuilder';
    }

    public function executeOnCustomerSave()
    {
        return true;
    }
}
