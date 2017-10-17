<?php

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

namespace CustomerManagementFrameworkBundle\Model\Objectbrick;

use CustomerManagementFrameworkBundle\Model\OAuth\OAuth1TokenInterface;
use Pimcore\Model\DataObject\Objectbrick\Data\AbstractData;

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
