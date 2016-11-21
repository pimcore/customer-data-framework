<?php
namespace CustomerManagementFramework\CustomerList\Exporter;

use Pimcore\Model\Object\Customer;

interface ExporterInterface
{
    /**
     * @return Customer\Listing
     */
    public function getListing();

    /**
     * @return array
     */
    public function getProperties();

    /**
     * Run the export
     */
    public function export();

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
