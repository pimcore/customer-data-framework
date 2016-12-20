<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:01
 */

namespace CustomerManagementFramework\SegmentBuilder;

use CustomerManagementFramework\Model\CustomerInterface;
use CustomerManagementFramework\Model\CustomerSegmentInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Pimcore\Model\Object\CustomerSegment;
use Psr\Log\LoggerInterface;

interface SegmentBuilderInterface {

    public function __construct($config, LoggerInterface $logger);

    /**
     * - prepares data and configurations which could be reused for all buildSegment(CustomerInterface $customer) calls
     *
     * @return void
     */
    public function prepare(SegmentManagerInterface $segmentManager);

    /**
     * update calculated segment(s) for given customer
     *
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function calculateSegments(CustomerInterface $customer, SegmentManagerInterface $segmentManager);

    /**
     * returns the unique name of the segment builder
     *
     * @return string
     */
    public function getName();

    /**
     * should this segment builder be executed on customer object save hook?
     *
     * @return mixed
     */
    public function executeOnCustomerSave();

    /**
     * executed in maintenance mode
     *
     * @return void
     */
    public function maintenance();
}