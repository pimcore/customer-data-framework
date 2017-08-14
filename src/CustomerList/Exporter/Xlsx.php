<?php

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

use Pimcore\Model\Object\Customer;

class Xlsx extends AbstractExporter
{
    const MIME_TYPE = 'text/csv';

    /**
     * @var \PHPExcel
     */
    protected $xlsx;

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
        $this->render($exportData);

        $objWriter = \PHPExcel_IOFactory::createWriter($this->xlsx, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        $this->generated = true;

        return $content;

    }

    public function getExtension()
    {
        return 'xlsx';
    }

    /**
     * @return $this
     */
    protected function render(array $exportData)
    {
        $this->stream = fopen('php://output', 'w+');

        $this->xlsx = new \PHPExcel();
        $this->xlsx->setActiveSheetIndex(0);
        $this->xlsx->getActiveSheet()->getStyle('1:1')->getFont()->setBold(true);

        $this->renderHeader();
        $rowIndex = 1;
        foreach ($exportData as $exportRow) {
            $rowIndex++;
            $this->renderRow($exportRow, $rowIndex);
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

        foreach($titles as $index => $title) {
            $this->xlsx->getActiveSheet()
                ->setCellValueByColumnAndRow($index, 1, $title)
                ->getColumnDimensionByColumn($index)
                ->setAutoSize(true);
        }

        return $this;
    }

    /**
     * @param [] $row
     * @return $this
     */
    protected function renderRow(array $row, $rowIndex)
    {

        foreach($row as $columnIndex => $value) {
            $this->xlsx->getActiveSheet()
                ->setCellValueByColumnAndRow($columnIndex, $rowIndex, $value);
        }

        return $this;
    }
}
