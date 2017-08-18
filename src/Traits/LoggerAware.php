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

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait LoggerAware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            return new NullLogger();
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
}
