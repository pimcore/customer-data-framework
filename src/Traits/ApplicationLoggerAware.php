<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Traits;

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

            $dbWriter = new ApplicationLoggerDb('notice');
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
