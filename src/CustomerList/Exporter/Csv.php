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
        $this->export();

        $stat = fstat($this->stream);

        return $stat['size'];
    }

    /**
     * Get export data
     *
     * @return string
     */
    public function getExportData()
    {
        $this->export();

        return stream_get_contents($this->stream, -1, 0);
    }

    /**
     * @return $this
     */
    protected function render()
    {
        $this->stream = fopen('php://temp', 'w+');

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
     * @param Customer $customer
     * @return $this
     */
    protected function renderRow(Customer $customer)
    {
        $row = [];
        foreach ($this->properties as $property) {
            $getter = 'get' . ucfirst($property);
            $value  = $customer->$getter();

            $row[] = $value;
        }

        fputcsv($this->stream, $row);

        return $this;
    }
}
