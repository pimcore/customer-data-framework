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

namespace CustomerManagementFrameworkBundle\ActivityManager;

use CustomerManagementFrameworkBundle\Model\ActivityInterface;

interface ActivityManagerInterface
{
    /**
     * Add/update activity in activity store.
     * Each activity is only saved once. The activity will be updated if it already exists in the store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function trackActivity(ActivityInterface $activity);

    /**
     * Delete activity from activity store.
     *
     * @param ActivityInterface $activity
     *
     * @return void
     */
    public function deleteActivity(ActivityInterface $activity);
}
