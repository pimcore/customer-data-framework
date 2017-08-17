<?php

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\Object\Listing;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param Request $request
     * @return []
     * @throws \Exception
     */
    public function getExportTmpData(Request $request);

    /**
     * @param $jobId
     * @param array $data
     * @return void
     */
    public function saveExportTmpData($jobId, array $data);

    /**
     * @param $jobId
     * @return void
     */
    public function deleteExportTmpData($jobId);

    /**
     * @return void
     */
    public function cleanupExportTmpData();
}
