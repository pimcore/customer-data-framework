<?php

declare(strict_types=1);

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
