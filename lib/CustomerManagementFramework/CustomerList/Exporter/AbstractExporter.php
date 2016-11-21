<?php

namespace CustomerManagementFramework\CustomerList\Exporter;

use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Customer;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * @var Customer\Listing
     */
    protected $listing;

    /**
     * Properties to export
     * @var array
     */
    protected $properties;

    /**
     * Path to result file
     * @var string
     */
    protected $outputFile;

    /**
     * @var bool
     */
    protected $rendered = false;

    /**
     * @param Customer\Listing $listing
     * @param array $properties
     */
    public function __construct(Customer\Listing $listing, array $properties)
    {
        $this->listing    = $listing;
        $this->properties = $properties;
    }

    /**
     * @return Customer\Listing
     */
    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Run the export
     */
    public function export()
    {
        if (!$this->rendered) {
            $this->render();
            $this->rendered = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    abstract protected function render();

    /**
     * @param $property
     * @return ClassDefinition\Data
     */
    protected function getPropertyDefinition($property)
    {
        $classDefinition = ClassDefinition::getById($this->listing->getClassId());
        $fieldDefintion  = $classDefinition->getFieldDefinition($property);

        if (!$fieldDefintion) {
            throw new \RuntimeException(sprintf('Failed to find field definition for field %s', $property));
        }

        return $fieldDefintion;
    }
}
