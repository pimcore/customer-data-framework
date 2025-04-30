<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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

    public function getServiceConfiguration(): array
    {
        return $this->serviceConfiguration;
    }

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
