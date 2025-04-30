<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\SegmentBuilder;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;

interface SegmentBuilderInterface
{
    /**
     * prepares data and configurations which could be reused for all buildSegment(CustomerInterface $customer) calls
     *
     *
     * @return void
     */
    public function prepare(SegmentManagerInterface $segmentManager);

    /**
     * update calculated segment(s) for given customer
     *
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
     *
     * @return void
     */
    public function maintenance(SegmentManagerInterface $segmentManager);
}
