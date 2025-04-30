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
