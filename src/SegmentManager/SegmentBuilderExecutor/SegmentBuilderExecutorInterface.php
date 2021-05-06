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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface SegmentBuilderExecutorInterface
{
    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function buildCalculatedSegmentsOnCustomerSave(CustomerInterface $customer);

    /**
     * Applies all SegmentBuilders to customers. If the param $changesQueueonly is set to true this is done only for customers which where changed since the last run.
     * If $segmentBuilderServiceId is given (symfony service id) then only this SegmentBuilder will be executed.
     *
     * @param bool $changesQueueOnly
     * @param string|null $segmentBuilderServiceId
     * @param int[]|null $customQueue Process only customer from given queue
     * @param bool|null $activeState Consider active-state, null : ignore, false -> inactive only, true -> active only
     * @param array $options
     *
     * @return void
     */
    public function buildCalculatedSegments(
        $changesQueueOnly = true,
        $segmentBuilderServiceId = null,
        array $customQueue = null,
        $activeState = null,
        $options = [],
        $captureSignals = false
    );

    /**
     * @param CustomerInterface $customer
     *
     * @return void
     */
    public function addCustomerToChangesQueue(CustomerInterface $customer);

    /**
     * Calls all maintenance methods of all SegmentBuilders
     *
     * @return void
     */
    public function executeSegmentBuilderMaintenance();
}
