<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 16:37
 */

namespace CustomerManagementFrameworkBundle\Command;

use Psr\Log\LoggerInterface;

abstract class AbstractCommand extends \Pimcore\Console\AbstractCommand
{
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return \Pimcore::getContainer()->get('cmf.logger');
    }
}
