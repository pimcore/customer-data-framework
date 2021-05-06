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

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;

interface SegmentBuilderInterface
{
    /**
     * prepares data and configurations which could be reused for all buildSegment(CustomerInterface $customer) calls
     *
     * @param SegmentManagerInterface $segmentManager
     *
     * @return void
     */
    public function prepare(SegmentManagerInterface $segmentManager);

    /**
     * update calculated segment(s) for given customer
     *
     * @param CustomerInterface $customer
     * @param SegmentManagerInterface $segmentManager
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
     * @param SegmentManagerInterface $segmentManager
     *
     * @return void
     */
    public function maintenance(SegmentManagerInterface $segmentManager);
}
