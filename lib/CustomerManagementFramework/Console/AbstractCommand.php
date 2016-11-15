<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:37
 */

namespace CustomerManagementFramework\Console;

use Monolog\Logger;

abstract class AbstractCommand extends \Pimcore\Console\AbstractCommand {

    /**
     * Get log level - default to info, but show all messages in verbose mode
     *
     * @return null|string
     */
    protected function getLogLevel()
    {
        $logLevel = Logger::NOTICE;
        if ($this->output->isVerbose()) {
            $logLevel = null;
        }

        return $logLevel;
    }

}