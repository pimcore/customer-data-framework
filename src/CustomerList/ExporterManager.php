<?php

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\Config;
use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\Object\Listing;
use Symfony\Component\HttpFoundation\Request;

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
     *
     * @return bool
     */
    public function hasExporter($key)
    {
        return isset($this->config->$key);
    }

    /**
     * @param $key
     * @param Listing\Concrete $listing
     *
     * @return ExporterInterface
     */
    public function buildExporter($key, Listing\Concrete $listing = null)
    {
        if (!$this->hasExporter($key)) {
            throw new \InvalidArgumentException(sprintf('Exporter %s is not defined', $key));
        }

        $config = $this->config->$key;

        $exporter = $config->exporter;
        /** @var ExporterInterface $exporter */
        $exporter = new $exporter($config->name, $config->properties->toArray(), (bool) $config->exportSegmentsAsColumns);

        if (null !== $listing) {
            $exporter->setListing($listing);
        }

        return $exporter;
    }

    /**
     * @param Request $request
     *
     * @return []
     *
     * @throws \Exception
     */
    public function getExportTmpData(Request $request)
    {
        if (!$jobId = $request->get('jobId')) {
            throw new \Exception('no jobId given');
        }

        $tmpFile = $this->getExportTmpFile($jobId);

        if (!file_exists($tmpFile)) {
            throw new \Exception('job with given jobId not found');
        }

        return json_decode(file_get_contents($tmpFile), true);
    }

    /**
     * @param $jobId
     * @param array $data
     */
    public function saveExportTmpData($jobId, array $data)
    {
        $tmpFile = $this->getExportTmpFile($jobId);

        file_put_contents($tmpFile, json_encode($data));
    }

    public function deleteExportTmpData($jobId)
    {
        $file = $this->getExportTmpFile($jobId);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * @return void
     */
    public function cleanupExportTmpData()
    {
        exec('find ' . PIMCORE_SYSTEM_TEMP_DIRECTORY . " -type f -atime +1 -iname 'cmf_customerexport*' -exec rm {} \;");
    }

    /**
     * @param $jobId
     *
     * @return string
     */
    protected function getExportTmpFile($jobId)
    {
        return PIMCORE_SYSTEM_TEMP_DIRECTORY . '/cmf_customerexport_' . $jobId . '.json';
    }
}
