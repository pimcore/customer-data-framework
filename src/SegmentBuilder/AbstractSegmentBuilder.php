<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\SegmentBuilder;

use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractSegmentBuilder implements SegmentBuilderInterface
{
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
