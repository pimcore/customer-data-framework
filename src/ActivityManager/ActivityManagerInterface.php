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

namespace CustomerManagementFrameworkBundle\ActivityManager;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;

interface ActivityManagerInterface
{
    /**
     * Add/update activity in activity store.
     * Each activity is only saved once. The activity will be updated if it already exists in the store.
     *
     *
     * @return void
     */
    public function trackActivity(ActivityInterface $activity);

    /**
     * Delete activity from activity store.
     *
     *
     * @return void
     */
    public function deleteActivity(ActivityInterface $activity);
}
