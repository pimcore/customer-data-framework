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

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

class Csv extends AbstractExporter
{
    const MIME_TYPE = 'text/csv';

    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var bool
     */
    protected $generated;

    /**
     * Get file MIME type
     *
     * @return string
     */
    public function getMimeType()
    {
        return static::MIME_TYPE;
    }

    /**
     * Get rendered file size
     *
     * @return int
     */
    public function getFilesize()
    {
        if (!$this->generated) {
            throw new \Exception('Export fore not generated: call generateExportFile before');
        }

        $stat = fstat($this->stream);

        return $stat['size'];
    }

    public function getExtension()
    {
        return 'csv';
    }

    public function generateExportFile(array $exportData)
    {
        $this->render($exportData);

        $this->generated = true;

        return stream_get_contents($this->stream, -1, 0);
    }

    /**
     * @return $this
     */
    protected function render(array $exportData)
    {
        $this->stream = fopen('php://temp', 'w+');

        $this->renderHeader($exportData);
        foreach ($this->getExportRows($exportData) as $exportRow) {
            $this->renderRow($this->getColumnValuesFromExportRow($exportRow));
        }

        return $this;
    }

    /**
     * @param array $exportData
     *
     * @return $this
     */
    protected function renderHeader(array $exportData)
    {
        $titles = $titles = $this->getHeaderTitles($exportData);

        fputcsv($this->stream, $titles);

        return $this;
    }

    /**
     * @param array $row
     *
     * @return $this
     */
    protected function renderRow(array $row)
    {
        fputcsv($this->stream, $row);

        return $this;
    }
}
