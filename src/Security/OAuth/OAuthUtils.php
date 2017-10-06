<?php

declare(strict_types=1);

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Security\OAuth;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;

class OAuthUtils extends \HWI\Bundle\OAuthBundle\Security\OAuthUtils
{
    /**
     * @param string $name
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner($name)
    {
        return parent::getResourceOwner($name);
    }
}
