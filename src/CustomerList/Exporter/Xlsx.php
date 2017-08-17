<?php

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Style;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\XLSX\Writer;
use Pimcore\Model\Object\Customer;

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
        if(!$this->generated) {
            throw new \Exception('Export fore not generated: call generateExportFile before');
        }

        $stat = fstat($this->stream);

        return $stat['size'];
    }


    public function generateExportFile(array $exportData)
    {

        $this->stream = fopen('php://output', 'w+');
        $writer = WriterFactory::create(Type::XLSX);
        $file = tempnam(PIMCORE_SYSTEM_TEMP_DIRECTORY, 'cmf_customerexport_');

        /**
         * @var Writer $writer
         */
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
        $this->renderHeader();
        $rowIndex = 1;
        foreach ($exportData as $exportRow) {
            $rowIndex++;
            $this->renderRow($exportRow, $rowIndex);
        }
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

        $style = new Style();
        $style->setFontBold();
        //$style->set


        $this->writer->addRowWithStyle($titles, $style);

        return $this;
    }

    /**
     * @param [] $row
     * @return $this
     */
    protected function renderRow(array $row, $rowIndex)
    {
        $this->writer->addRow($row);

        return $this;
    }
}
