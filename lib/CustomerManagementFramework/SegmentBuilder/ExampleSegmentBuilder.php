<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:25
 */

namespace CustomerManagementFramework\SegmentBuilder;

use CustomerManagementFramework\Factory;
use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Pimcore\Model\Object\Customer\Listing;
use Pimcore\Model\Object\CustomerSegment;
use Psr\Log\LoggerInterface;

class ExampleSegmentBuilder implements SegmentBuilderInterface {

    private $config;
    private $logger;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * - prepares data and configurations which could be reused for all buildSegment(CustomerInterface $customer) calls
     * - return list of all customers where the segment builder should be applied to
     *
     * @return \Pimcore\Model\Object\Customer\Listing
     */
    public function prepare()
    {
        $this->logger->notice("prepare example segment builder");

        return new Listing();
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
        $this->logger->notice(sprintf("build segment for customer %s",(string)$customer));

        $addSegments = [];

        $addSegments[] = $segmentManager->createCalculatedSegment('A-Kunde', $this->getName());

        $segmentManager->mergeCalculatedSegments($customer, $addSegments);
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return "ExampleSegmentBuilder";
    }


}