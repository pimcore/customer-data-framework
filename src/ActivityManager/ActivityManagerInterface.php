<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
