<?php

namespace CustomerManagementFramework\CustomerList\Exporter;

use Pimcore\Model\Object\ClassDefinition;
use Pimcore\Model\Object\Customer;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * @var string
     */
    protected $name;

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
     * @var bool
     */
    protected $rendered = false;

    /**
     * @param $name
     * @param array $properties
     */
    public function __construct($name, array $properties)
    {
        $this->setName($name);
        $this->setProperties($properties);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->reset();
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
     * @param Customer\Listing $listing
     */
    public function setListing(Customer\Listing $listing)
    {
        $this->reset();
        $this->listing = $listing;
    }

    /**
     * Run the export
     */
    public function export()
    {
        if (null === $this->listing) {
            throw new \RuntimeException('Listing is not set');
        }

        if (!$this->rendered) {
            $this->render();
            $this->rendered = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function reset()
    {
        $this->rendered = false;

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

        return $classDefinition->getFieldDefinition($property);
    }
}
