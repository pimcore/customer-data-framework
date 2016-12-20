<?php

namespace CustomerManagementFramework\Mailchimp\ExportToolkit\Interpreter;

use ExportToolkit\ExportService\IInterpreter;

/**
 * Convert boolean status to subscribed/unsubscribed string
 */
class Status implements IInterpreter
{
    public static function interpret($value, $config = null)
    {
        if ($value) {
            return 'subscribed';
        }

        return 'unsubscribed';
    }
}
