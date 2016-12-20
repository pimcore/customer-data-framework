<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:25
 */

namespace CustomerManagementFramework\SegmentBuilder;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;
use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

class StateSegmentBuilder implements SegmentBuilderInterface {

    private $config;
    private $logger;
    private $countryTransformers;
    private $groupName;
    private $segmentGroup;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;

        $this->countryTransformers = $config->countryTransformers ? $config->countryTransformers->toArray() : [
            'AT' => 'CustomerManagementFramework\DataTransformer\Zip2State\At',
            'DE' => 'CustomerManagementFramework\DataTransformer\Zip2State\De',
            'CH' => 'CustomerManagementFramework\DataTransformer\Zip2State\Ch',
        ];

        $this->logger = $logger;

        $this->groupName = (string)$config->segmentGroup ? : 'State';
    }

    /**
     * prepare data and configurations which could be reused for all calculateSegments() calls
     *
     * @return \Pimcore\Model\Object\Customer\Listing
     */
    public function prepare(SegmentManagerInterface $segmentManager)
    {

        $this->segmentGroup = $segmentManager->createSegmentGroup($this->groupName, $this->groupName, true);

        foreach($this->countryTransformers as $key => $transformer) {
            $transformer = Factory::getInstance()->createObject($transformer, DataTransformerInterface::class);
            $this->countryTransformers[$key] = $transformer;
        }
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
        $countryCode = $customer->getCountryCode();

        $stateSegment = null;

        if(isset($this->countryTransformers[$countryCode])) {
            $transformer = $this->countryTransformers[$countryCode];

            if($state = $transformer->transform($customer->getZip())) {
                $stateSegment = $segmentManager->createCalculatedSegment($countryCode . " - " . $state, $this->groupName);
            }
        }

        $segments = [];
        if($stateSegment) {
            $segments[] = $stateSegment;
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
        return "StateSegmentBuilder";
    }

    public function executeOnCustomerSave()
    {
        return true;
    }


}