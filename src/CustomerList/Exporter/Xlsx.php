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

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class Xlsx extends AbstractExporter
{
    const MIME_TYPE = 'text/csv';

    /**
     * @var Writer
     */
    protected $writer;

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

        return $stat['size'] ?? 0;
    }

    public function generateExportFile(array $exportData)
    {
        $this->stream = fopen('php://output', 'w+');
        $writer = new Writer(new Options());
        $file = tempnam(PIMCORE_SYSTEM_TEMP_DIRECTORY, 'cmf_customerexport_');

        $writer->openToFile($file);
        $this->writer = $writer;

        $this->render($exportData);
        $writer->close();

        $content = file_get_contents($file);
        unlink($file);

        $this->generated = true;

        return $content;
    }

    public function getExtension()
    {
        return 'xlsx';
    }

    protected function render(array $exportData)
    {
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
        $titles = $this->getHeaderTitles($exportData);

        $style = new Style();
        $style->setFontBold();

        $this->writer->addRow(Row::fromValues($titles, $style));

        return $this;
    }

    /**
     * @param array $row
     *
     * @return $this
     */
    protected function renderRow(array $row)
    {
        $this->writer->addRow(Row::fromValues($row));

        return $this;
    }
}
