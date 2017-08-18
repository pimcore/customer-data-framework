<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle;

class Config
{
    private static function getConfigFile()
    {
        return \Pimcore\Config::locateConfigFile('plugins/CustomerManagementFramework/config.php');
    }

    protected static $config = null;

    public static function getConfig()
    {
        if (is_null(self::$config)) {
            $file = self::getConfigFile();

            if (file_exists($file)) {
                self::$config = new \Pimcore\Config\Config(require($file), true);
            } else {
                throw new \Exception($file." doesn't exist");
            }
        }

        return self::$config;
    }
}
