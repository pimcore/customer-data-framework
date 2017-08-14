<?php

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\Object\Listing;

interface ExporterManagerInterface
{
    /**
     * @return \Pimcore\Config
     */
    public function getConfig();

    /**
     * @param $key
     * @return bool
     */
    public function hasExporter($key);

    /**
     * @param $key
     * @param Listing\Concrete $listing
     * @return ExporterInterface
     */
    public function buildExporter($key, Listing\Concrete $listing = null);
}
