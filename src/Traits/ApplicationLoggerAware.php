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

namespace CustomerManagementFrameworkBundle\Traits;

use Pimcore\Db;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Log\Handler\ApplicationLoggerDb;
use Psr\Log\LoggerInterface;

trait ApplicationLoggerAware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $loggerComponent;

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $logger = new ApplicationLogger();

            if(\Pimcore\Version::getRevision() < 143) {
                $dbWriter = new ApplicationLoggerDb('notice');
            } else {
                $dbWriter = new ApplicationLoggerDb(Db::get(),'notice');
            }
            $logger->addWriter($dbWriter);

            if ($this->loggerComponent) {
                $logger->setComponent($this->loggerComponent);
            }

            $cmfLogger = \Pimcore::getContainer()->get('cmf.logger');
            if ($cmfLogger instanceof \Monolog\Logger) {
                if ($handlers = $cmfLogger->getHandlers()) {
                    foreach ($handlers as $handler) {
                        $logger->addWriter($handler);
                    }
                }
            }

            return $logger;
        }

        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoggerComponent()
    {
        return $this->loggerComponent;
    }

    /**
     * Sets the logger component of the application logger. Needs to be called before the first getLogger() call.
     *
     * @param string $loggerComponent
     */
    public function setLoggerComponent($loggerComponent)
    {
        $this->loggerComponent = $loggerComponent;
    }
}
