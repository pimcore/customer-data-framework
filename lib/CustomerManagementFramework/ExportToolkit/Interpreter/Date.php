<?php

namespace CustomerManagementFramework\ExportToolkit\Interpreter;

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
