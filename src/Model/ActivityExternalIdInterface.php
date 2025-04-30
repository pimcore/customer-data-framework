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
