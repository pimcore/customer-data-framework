<?php

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

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
     * Get extension of exported file
     *
     * @return string
     */
    public function getExtension();

    /**
     * Get export data
     *
     * @return []
     */
    public function getExportData();

    /**
     * Generates the export file from given export data.
     *
     * @param array $exportData
     * @return mixed
     */
    public function generateExportFile(array $exportData);
}
