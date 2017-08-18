<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Testing\Fixtures\View\Formatter;

class ToStringObject
{
    const TO_STRING_VALUE = 'I implement __toString()';

    /**
     * @return string
     */
    public function __toString()
    {
        return static::TO_STRING_VALUE;
    }
}
