<?php

namespace CustomerManagementFramework\CustomerList;

use CustomerManagementFramework\CustomerList\Exporter\ExporterInterface;
use CustomerManagementFramework\Plugin;
use Pimcore\Model\Object\Customer;

class ExporterManager implements ExporterManagerInterface
{
    /**
     * @var \Zend_Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = Plugin::getConfig()->CustomerList->exporters;
    }

    /**
     * @return \Zend_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasExporter($key)
    {
        return isset($this->config->$key);
    }

    /**
     * @param $key
     * @param Customer\Listing $listing
     * @return ExporterInterface
     */
    public function buildExporter($key, Customer\Listing $listing = null)
    {
        if (!$this->hasExporter($key)) {
            throw new \InvalidArgumentException(sprintf('Exporter %s is not defined', $key));
        }

        $config = $this->config->$key;

        /** @var ExporterInterface $exporter */
        $exporter = \Pimcore::getDiContainer()->make($config->exporter, [
            'name'       => $config->name,
            'properties' => $config->properties->toArray()
        ]);

        if (null !== $listing) {
            $exporter->setListing($listing);
        }

        return $exporter;
    }
}
