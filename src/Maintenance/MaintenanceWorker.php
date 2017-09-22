<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-22
 * Time: 9:16 AM
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
class MaintenanceWorker {

    /**
     * @var array ['method' => service]
     */
    private $serviceConfiguration = [];

    public function __construct(array $serviceConfiguration) {
        $this->setServiceConfiguration($serviceConfiguration);
    }

    /**
     * @return array
     */
    public function getServiceConfiguration(): array {
        return $this->serviceConfiguration;
    }

    /**
     * @param array $serviceConfiguration
     */
    public function setServiceConfiguration(array $serviceConfiguration) {
        $this->serviceConfiguration = $serviceConfiguration;
    }

    /**
     * calls the respective method on each service received via DI
     */
    public function execute() {
        foreach($this->getServiceConfiguration() as $call => $service) {
            $service->$call();
        }
    }
}