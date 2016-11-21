<?php
namespace CustomerManagementFramework\CustomerList\Exporter;

use Pimcore\Model\Object\Customer;

interface ExporterInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param array $properties
     */
    public function setProperties(array $properties);

    /**
     * @return Customer\Listing
     */
    public function getListing();

    /**
     * @param Customer\Listing $listing
     */
    public function setListing(Customer\Listing $listing);

    /**
     * Get file MIME type
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Get rendered file size
     *
     * @return int
     */
    public function getFilesize();

    /**
     * Get export data
     *
     * @return mixed
     */
    public function getExportData();
}
