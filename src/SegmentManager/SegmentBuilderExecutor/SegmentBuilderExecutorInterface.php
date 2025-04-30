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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentBuilderExecutor;

use CustomerManagementFrameworkBundle\Model\CustomerInterface;

interface SegmentBuilderExecutorInterface
{
    /**
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
        ?array $customQueue = null,
        $activeState = null,
        $options = [],
        $captureSignals = false
    );

    /**
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
