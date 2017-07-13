<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 16/06/2017
 * Time: 11:29
 */

namespace CustomerManagementFrameworkBundle;

class Config
{

    private static function getConfigFile()
    {
        return \Pimcore\Config::locateConfigFile("plugins/CustomerManagementFramework/config.php");
    }

    protected static $config = null;

    public static function getConfig()
    {
        if (is_null(self::$config)) {
            $file = self::getConfigFile();

            if (file_exists($file)) {
                self::$config = new \Pimcore\Config\Config(require($file), true);;

            } else {
                throw new \Exception($file." doesn't exist");
            }
        }


        return self::$config;
    }
}