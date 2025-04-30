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

use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Listing\Concrete;
use Pimcore\Model\DataObject\Service;

abstract class AbstractExporter implements ExporterInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Concrete
     */
    protected $listing;

    /**
     * Properties to export
     *
     * @var array
     */
    protected $properties;

    /**
     * @var bool
     */
    protected $exportSegmentsAsColumns;

    /**
     * remember column order of segment column headers if $exportSegmentsAsColumns is enabled
     *
     * @var array
     */
    protected $segmentColumnOrder = [];

    const COLUMNS = 'columns';

    const ROWS = 'rows';

    const SEGMENT_IDS = 'segmentIds';

    /**
     * @param string $name
     * @param bool $exportSegmentsAsColumns
     */
    public function __construct($name, array $properties, $exportSegmentsAsColumns)
    {
        $this->setName($name);
        $this->setProperties($properties);
        $this->setExportSegmentsAsColumns($exportSegmentsAsColumns);
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

    public function setProperties(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @return Concrete
     */
    public function getListing()
    {
        return $this->listing;
    }

    public function setListing(Concrete $listing)
    {
        $this->listing = $listing;
    }

    /**
     * Run the export
     */
    public function getExportData()
    {
        if (null === $this->listing) {
            throw new \RuntimeException('Listing is not set');
        }

        $rows = [];
        $allSegmentIds = [];
        /** @var \Pimcore\Model\DataObject\Concrete&CustomerInterface $customer */
        foreach ($this->listing as $customer) {
            $classDefinition = $customer->getClass();
            $row = [self::COLUMNS => [], self::SEGMENT_IDS => []];
            foreach ($this->properties as $property) {
                if ($fd = $classDefinition->getFieldDefinition($property)) {
                    $value = $fd->getForCsvExport($customer);
                } else {
                    $getter = 'get'.ucfirst($property);
                    $value = $customer->$getter();
                }

                $row[self::COLUMNS][] = (string) $value;
            }

            if ($this->getExportSegmentsAsColumns()) {
                if ($segments = $customer->getAllSegments()) {
                    foreach ($segments as $segment) {
                        $row[self::SEGMENT_IDS][] = $segment->getId();
                        $allSegmentIds[] = $segment->getId();
                    }
                }
            }

            $row[self::COLUMNS] = Service::escapeCsvRecord($row[self::COLUMNS]);
            $row[self::SEGMENT_IDS] = Service::escapeCsvRecord($row[self::SEGMENT_IDS]);
            $rows[] = $row;
        }

        return [
            self::ROWS => $rows,
            self::SEGMENT_IDS => array_unique($allSegmentIds),
        ];
    }

    /**
     * @return bool
     */
    public function getExportSegmentsAsColumns()
    {
        return $this->exportSegmentsAsColumns;
    }

    /**
     * @param bool $exportSegmentsAsColumns
     */
    public function setExportSegmentsAsColumns($exportSegmentsAsColumns)
    {
        $this->exportSegmentsAsColumns = $exportSegmentsAsColumns;
    }

    /**
     *
     * @return array
     */
    protected function getHeaderTitles(array $exportData)
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

        if ($this->getExportSegmentsAsColumns() && sizeof($exportData[self::SEGMENT_IDS])) {
            $list = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegments();
            array_walk($exportData[self::SEGMENT_IDS], 'intval');
            $list->addConditionParam('id in(' . implode(', ', $exportData[self::SEGMENT_IDS]) .')');
            $list->setOrderKey('concat(' . $list->quoteIdentifier('path') .', '. $list->quoteIdentifier('key') . ')', false);

            $i = sizeof($titles);
            foreach ($list as $item) {
                $segmentName = [];
                if ($group = $item->getGroup()) {
                    $segmentName[] = $group->getName() ?: $group->getReference();
                }
                $segmentName[] = $item->getName() ?: $item->getReference();
                $title = 'Segment ' . implode(':', $segmentName);
                $titles[] = $title;
                $this->segmentColumnOrder[$item->getId()] = $i;
                $i++;
            }
        }

        return $titles;
    }

    /**
     *
     * @return array
     */
    protected function getExportRows(array $exportData)
    {
        return $exportData[self::ROWS];
    }

    protected function getColumnValuesFromExportRow($exportRow)
    {
        $columns = $exportRow[self::COLUMNS];

        if (is_array($exportRow[self::SEGMENT_IDS])) {
            foreach ($this->segmentColumnOrder as $column) {
                $columns[$column] = 0;
            }
            foreach ($exportRow[self::SEGMENT_IDS] as $id) {
                $columns[$this->segmentColumnOrder[$id]] = '1';
            }
        }

        return $columns;
    }

    /**
     * @return $this
     */
    abstract protected function render(array $exportData);

    /**
     * @param string $property
     *
     * @return ClassDefinition\Data|null
     */
    protected function getPropertyDefinition($property)
    {
        $classDefinition = ClassDefinition::getById($this->listing->getClassId());

        return $classDefinition->getFieldDefinition($property);
    }
}
