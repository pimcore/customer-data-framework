<?php

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

use Pimcore\Model\Object\Customer;

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
        if(!$this->generated) {
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

        $this->renderHeader();
        foreach ($exportData as $exportRow) {
            $this->renderRow($exportRow);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function renderHeader()
    {
        $titles = [];
        foreach ($this->properties as $property) {
            $definition = $this->getPropertyDefinition($property);
            if ($definition) {
                $titles[] = $definition->getTitle();
            } else {
                $titles[] = $property;
            }
        }

        fputcsv($this->stream, $titles);

        return $this;
    }

    /**
     * @param [] $row
     * @return $this
     */
    protected function renderRow(array $row)
    {
        fputcsv($this->stream, $row);

        return $this;
    }
}
