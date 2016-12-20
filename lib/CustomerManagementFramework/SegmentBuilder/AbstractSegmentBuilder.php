<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:01
 */

namespace CustomerManagementFramework\SegmentBuilder;

use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractSegmentBuilder implements SegmentBuilderInterface {
    private $config;
    private $logger;

    public function __construct($config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * return the name of the segment builder
     *
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    public function executeOnCustomerSave()
    {
        return false;
    }

    public function maintenance(SegmentManagerInterface $segmentManager)
    {

    }
}