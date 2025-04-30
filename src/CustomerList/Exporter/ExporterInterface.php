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

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

use Pimcore\Model\DataObject\Listing\Concrete;

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

    public function setProperties(array $properties);

    /**
     * @return Concrete
     */
    public function getListing();

    public function setListing(Concrete $listing);

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
     * @return array
     */
    public function getExportData();

    /**
     * Generates the export file from given export data.
     *
     *
     * @return mixed
     */
    public function generateExportFile(array $exportData);
}
