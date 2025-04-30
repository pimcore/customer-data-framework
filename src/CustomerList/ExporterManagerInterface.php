<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\HttpFoundation\Request;

interface ExporterManagerInterface
{
    /**
     * @return array
     */
    public function getExporterConfig();

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasExporter($key);

    /**
     * @param string $key
     *
     * @return ExporterInterface
     */
    public function buildExporter($key, ?Listing\Concrete $listing = null);

    /**
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getExportTmpData(Request $request);

    /**
     * @param string $jobId
     *
     * @return void
     */
    public function saveExportTmpData($jobId, array $data);

    /**
     * @param string $jobId
     *
     * @return void
     */
    public function deleteExportTmpData($jobId);

    /**
     * @return void
     */
    public function cleanupExportTmpData();
}
