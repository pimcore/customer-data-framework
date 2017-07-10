<?php

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use CustomerManagementFrameworkBundle\Plugin;
use Pimcore\Model\Object\Customer;

class ExporterManager implements ExporterManagerInterface
{
    /**
     * @var \Pimcore\Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = Config::getConfig()->CustomerList->exporters;
    }

    /**
     * @return \Pimcore\Config
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

        $exporter = $config->exporter;
        /** @var ExporterInterface $exporter */
        $exporter = new $exporter($config->name, $config->properties->toArray());


        if (null !== $listing) {
            $exporter->setListing($listing);
        }

        return $exporter;
    }
}
