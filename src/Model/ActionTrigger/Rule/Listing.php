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

namespace CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;

use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use Doctrine\DBAL\Exception;
use Pimcore\Model\Listing\AbstractListing;

/**
 * @method Listing\Dao getDao()
 */
class Listing extends AbstractListing
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function isValidOrderKey(/* string */ $key): bool
    {
        return true;
    }

    /**
     * @return Rule[]
     *
     * @throws Exception
     */
    public function load(): array
    {
        return $this->getDao()->load();
    }
}
