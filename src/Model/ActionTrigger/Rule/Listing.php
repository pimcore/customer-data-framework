<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;

use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use Pimcore\Model\Listing\AbstractListing;

class Listing extends AbstractListing
{
    public function isValidOrderKey($key)
    {
        return true;
    }

    /**
     * @return Rule[]
     */
    public function load()
    {
        return $this->getDao()->load();
    }
}
