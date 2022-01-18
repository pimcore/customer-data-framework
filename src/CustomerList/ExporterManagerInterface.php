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
     * @param Listing\Concrete|null $listing
     *
     * @return ExporterInterface
     */
    public function buildExporter($key, Listing\Concrete $listing = null);

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getExportTmpData(Request $request);

    /**
     * @param string $jobId
     * @param array $data
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
