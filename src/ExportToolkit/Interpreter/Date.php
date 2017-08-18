<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ExportToolkit\Interpreter;

use ExportToolkit\ExportService\IInterpreter;

/**
 * Formats a DateTime with the given format
 */
class Date implements IInterpreter
{
    const DEFAULT_FORMAT = 'd/m';

    /**
     * @param $value
     * @param null $config
     *
     * @return string
     */
    public static function interpret($value, $config = null)
    {
        if (!$value) {
            return $value;
        }

        $config = (array)$config;
        $format = (isset($config['format'])) ? $config['format'] : static::DEFAULT_FORMAT;

        if ($value instanceof \DateTime) {
            return $value->format($format);
        }
    }
}
