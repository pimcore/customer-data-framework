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

interface ActivityExternalIdInterface extends ActivityInterface
{
    /**
     * Returns external ID of the activity. Needed in order to be able to update the entry in the activity store based on this ID.
     *
     * @return string/int
     */
    public function getId();
}
