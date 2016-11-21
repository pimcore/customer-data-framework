<?php

namespace CustomerManagementFramework\CustomerList\Exporter;

use Pimcore\Model\Object\Customer;

class Csv extends AbstractExporter
{
    const MIME_TYPE = 'text/csv';

    /**
     * @var \SplTempFileObject
     */
    protected $file;

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
        return $this->export()->file->getSize();
    }

    /**
     * Get export data
     *
     * @return mixed
     */
    public function getExportData()
    {
        $this->export();

        return $this->file->fread($this->file->getSize());
    }

    /**
     * @return $this
     */
    protected function render()
    {
        $this->file = new \SplTempFileObject(0);

        $this->renderHeader();
        foreach ($this->listing as $customer) {
            $this->renderRow($customer);
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
            // $definition = $this->getPropertyDefinition($property);
            // $titles[]   = $definition->getTitle();

            $titles[] = $property;
        }

        $this->file->fputcsv($titles);

        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    protected function renderRow(Customer $customer)
    {
        $row = [];

        foreach ($this->properties as $property) {
            // $definition = $this->getPropertyDefinition($property);

            $getter = 'get' . ucfirst($property);
            $value  = $customer->$getter;

            $row[] = $value;
        }

        $this->file->fputcsv($row);

        return $this;
    }
}
