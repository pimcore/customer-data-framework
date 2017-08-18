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

use CustomerManagementFrameworkBundle\Model\OAuth\OAuth1TokenInterface;
use Pimcore\Model\Object\Objectbrick\Data\AbstractData;

abstract class AbstractOAuth1Token extends AbstractData implements OAuth1TokenInterface
{
    /**
     * @return array
     */
    public function getSecureProperties()
    {
        return [
            'token',
            'tokenSecret',
        ];
    }
}
