<?php
namespace CustomerManagementFramework\CustomerList;

use CustomerManagementFramework\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\Object\Customer;

interface ExporterManagerInterface
{
    /**
     * @param $key
     * @return bool
     */
    public function hasExporter($key);

    /**
     * @param $key
     * @param Customer\Listing $listing
     * @return ExporterInterface
     */
    public function buildExporter($key, Customer\Listing $listing = null);
}
