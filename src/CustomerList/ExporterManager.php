<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\HttpFoundation\Request;

class ExporterManager implements ExporterManagerInterface
{
    /**
     * @var array
     */
    protected $exporterConfig;

    public function __construct(array $exporterConfig = [])
    {
        $this->exporterConfig = $exporterConfig;
    }

    public function getExporterConfig()
    {
        return $this->exporterConfig;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasExporter($key)
    {
        return isset($this->exporterConfig[$key]);
    }

    /**
     * @param string $key
     * @param Listing\Concrete|null $listing
     *
     * @return ExporterInterface
     */
    public function buildExporter($key, Listing\Concrete $listing = null)
    {
        if (!$this->hasExporter($key)) {
            throw new \InvalidArgumentException(sprintf('Exporter %s is not defined', $key));
        }

        $config = $this->exporterConfig[$key];

        $exporter = $config['exporter'];
        /** @var ExporterInterface $exporter */
        $exporter = new $exporter($config['name'], $config['properties'], $config['exportSegmentsAsColumns']);

        if (null !== $listing) {
            $exporter->setListing($listing);
        }

        return $exporter;
    }

    /**
     * @param Request $request
     *
     * @return array
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
     * @param string $jobId
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
     * @param string $jobId
     *
     * @return string
     */
    protected function getExportTmpFile($jobId)
    {
        return PIMCORE_SYSTEM_TEMP_DIRECTORY . '/cmf_customerexport_' . $jobId . '.json';
    }
}
