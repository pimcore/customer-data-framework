<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Model\Objectbrick;

use CustomerManagementFrameworkBundle\Model\OAuth\OAuth2TokenInterface;
use Pimcore\Model\DataObject\Objectbrick\Data\AbstractData;

abstract class AbstractOAuth2Token extends AbstractData implements OAuth2TokenInterface
{
    /**
     * @return array
     */
    public function getSecureProperties()
    {
        return [
            'accessToken',
            'refreshToken',
        ];
    }
}
