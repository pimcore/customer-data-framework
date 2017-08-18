<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model;

interface PersistentActivityInterface extends ActivityInterface
{
    /**
     * save activity
     *
     * @return void
     */
    public function save();

    /**
     * delete activity
     *
     * @return void
     */
    public function delete();
}
