<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\CustomerList;

use CustomerManagementFrameworkBundle\CustomerList\Exporter\ExporterInterface;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\HttpFoundation\Request;

interface ExporterManagerInterface
{
    /**
     * @return []
     */
    public function getExporterConfig();

    /**
     * @param $key
     *
     * @return bool
     */
    public function hasExporter($key);

    /**
     * @param $key
     * @param Listing\Concrete $listing
     *
     * @return ExporterInterface
     */
    public function buildExporter($key, Listing\Concrete $listing = null);

    /**
     * @param Request $request
     *
     * @return []
     *
     * @throws \Exception
     */
    public function getExportTmpData(Request $request);

    /**
     * @param $jobId
     * @param array $data
     *
     * @return void
     */
    public function saveExportTmpData($jobId, array $data);

    /**
     * @param $jobId
     *
     * @return void
     */
    public function deleteExportTmpData($jobId);

    /**
     * @return void
     */
    public function cleanupExportTmpData();
}
