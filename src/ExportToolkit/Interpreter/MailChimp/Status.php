<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\ExportToolkit\Interpreter\MailChimp;

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
