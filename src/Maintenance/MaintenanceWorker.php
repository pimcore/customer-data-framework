<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Maintenance;

/**
 * Class MaintenanceWorker
 *
 * receives a configuration in the form of ['method' => 'service']
 * and calls the respective method on each service
 *
 * @package CustomerManagementFrameworkBundle\Maintenance
 */
class MaintenanceWorker
{
    /**
     * @var array ['method' => service]
     */
    private $serviceConfiguration = [];

    public function __construct(array $serviceConfiguration)
    {
        $this->setServiceConfiguration($serviceConfiguration);
    }

    /**
     * @return array
     */
    public function getServiceConfiguration(): array
    {
        return $this->serviceConfiguration;
    }

    /**
     * @param array $serviceConfiguration
     */
    public function setServiceConfiguration(array $serviceConfiguration)
    {
        $this->serviceConfiguration = $serviceConfiguration;
    }

    /**
     * calls the respective method on each service received via DI
     */
    public function execute()
    {
        foreach ($this->getServiceConfiguration() as $call => $service) {
            $service->$call();
        }
    }
}
