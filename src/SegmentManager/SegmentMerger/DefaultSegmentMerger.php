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

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentMerger;

use CustomerManagementFrameworkBundle\CustomerSaveManager\CustomerSaveManagerInterface;
use CustomerManagementFrameworkBundle\Helper\Notes;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentManagerInterface;
use CustomerManagementFrameworkBundle\SegmentManager\SegmentMerger\DefaultSegmentMerger\MetadataFiller;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Cache\RuntimeCache;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\Element\Note;

class DefaultSegmentMerger implements SegmentMergerInterface
{
    use LoggerAware;

    /**
     * @var CustomerSaveManagerInterface
     */
    protected $customerSaveManager;

    /**
     * @var SegmentManagerInterface
     */
    protected $segmentManager;

    /**
     * @var MetadataFiller
     */
    protected $metadataFiller;

    /**
     * @var array
     */
    protected $mergedSegmentsCustomerSaveQueue;

    public function __construct(CustomerSaveManagerInterface $customerSaveManager, SegmentManagerInterface $segmentManager, MetadataFiller $metadataFiller)
    {
        $this->customerSaveManager = $customerSaveManager;
        $this->segmentManager = $segmentManager;
        $this->metadataFiller = $metadataFiller;
    }

    /**
     * @inheritdoc
     */
    public function mergeSegments(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $hintForNotes = null,
        $segmentCreatedTimestamp = null,
        $segmentApplicationCounter = null
    ) {
        list($addManualSegments, $addCalculatedSegments) = $this->devideIntoManualAndCalculatedSegments($addSegments);
        list($deleteManualSegments, $deleteCalculatedSegments) = $this->devideIntoManualAndCalculatedSegments($deleteSegments);

        if (sizeof($addCalculatedSegments) || sizeof($deleteCalculatedSegments)) {
            $this->mergeSegmentsHelper(
                $customer,
                $addCalculatedSegments,
                $deleteCalculatedSegments,
                true,
                $hintForNotes,
                $segmentCreatedTimestamp,
                $segmentApplicationCounter
            );
        }

        if (sizeof($addManualSegments) || sizeof($deleteManualSegments)) {
            $this->mergeSegmentsHelper(
                $customer,
                $addManualSegments,
                $deleteManualSegments,
                false,
                $hintForNotes,
                $segmentCreatedTimestamp,
                $segmentApplicationCounter
            );
        }
    }

    /**
     * @param CustomerSegmentInterface[] $segments
     *
     * @return array
     */
    private function devideIntoManualAndCalculatedSegments(array $segments)
    {
        $manualSegments = [];
        $calculatedSegments = [];
        foreach ($segments as $segment) {
            if ($segment->getCalculated()) {
                $calculatedSegments[] = $segment;
            } else {
                $manualSegments[] = $segment;
            }
        }

        return [$manualSegments, $calculatedSegments];
    }

    /**
     * @param CustomerInterface $customer
     * @param array $addSegments
     * @param array $deleteSegments
     * @param bool $calculated
     * @param string $hintForNotes
     * @param int|true|null $segmentCreatedTimestamp
     * @param int|true|null $segmentApplicationCounter
     */
    protected function mergeSegmentsHelper(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments,
        $calculated,
        $hintForNotes,
        $segmentCreatedTimestamp = null,
        $segmentApplicationCounter = null
    ) {
        $currentSegments = $this->getSegmentsDataFromCustomer($customer, $calculated);

        $saveNeeded = false;
        $addSegments = $this->convertToSegmentRelationFieldType($addSegments, $calculated);

        if ($addedSegments = $this->addSegmentsToArray($currentSegments, $addSegments)) {
            $saveNeeded = true;
        }

        if ($removedSegments = $this->removeSegmentsFromArray($currentSegments, $deleteSegments)) {
            $saveNeeded = true;
        }

        $this->setSegmentsDataOfCustomer($customer, $currentSegments, $calculated);

        $saveNeeded = $this->metadataFiller->mergedSegmentsFillUpMetadata(
            $customer,
            $addSegments,
            ($addedSegments ?: []),
            $calculated,
            $segmentCreatedTimestamp,
            $segmentApplicationCounter,
            $saveNeeded,
            $this
        );

        if ($saveNeeded) {
            $notes = [];

            if (is_array($removedSegments) && sizeof($removedSegments)) {
                $notes = array_merge(
                    $notes,
                    $this->createNotes($customer, $removedSegments, 'Segment(s) removed', $hintForNotes)
                );
            }

            if (is_array($addedSegments) && sizeof($addedSegments)) {
                $notes = array_merge(
                    $notes,
                    $this->createNotes($customer, $addedSegments, 'Segment(s) added', $hintForNotes)
                );
            }

            $this->addToMergedSegmentsCustomerSaveQueue($customer, $notes);
        }
    }

    /**
     * @param array $segments
     * @param array $addSegments
     *
     * @return CustomerSegmentInterface[]|false
     */
    protected function addSegmentsToArray(&$segments, $addSegments)
    {
        $addedSegments = Objects::addObjectsToArray($segments, $addSegments);

        if ($addedSegments) {
            $addedSegments = $this->objectMetadataArrayToObjectArray($addedSegments);
        }

        return $addedSegments;
    }

    protected function convertToSegmentRelationFieldType(array $segments, $calculated = false)
    {
        if (!sizeof($segments)) {
            return $segments;
        }

        if (!$this->hasObjectMetadataSegmentsField($calculated)) {
            return $segments;
        }

        $fieldname = $calculated ? 'calculatedSegments' : 'manualSegments';

        foreach ($segments as $key => $segment) {
            $objectMetadata = new ObjectMetadata($fieldname, ['created_timestamp', 'application_counter'], $segment);
            $segments[$key] = $objectMetadata;
        }

        return $segments;
    }

    /**
     *
     * @param bool $calculated
     *
     * @return bool
     */
    public function hasObjectMetadataSegmentsField($calculated = false)
    {
        $classId = \Pimcore::getContainer()->get('cmf.customer_provider')->getCustomerClassId();

        $cacheKey = 'CMFSegmentMergerHasObjectMetdataField' . $classId;

        if ($calculated) {
            $cacheKey .= 'Calculated';
        } else {
            $cacheKey .= 'Manual';
        }

        $fieldname = $calculated ? 'calculatedSegments' : 'manualSegments';

        if (!RuntimeCache::isRegistered($cacheKey)) {
            $classDefinition = ClassDefinition::getById($classId);
            $fd = $classDefinition->getFieldDefinition($fieldname);

            $hasObjectMetdataSegmentsField = $fd instanceof ClassDefinition\Data\AdvancedManyToManyObjectRelation;

            RuntimeCache::save($hasObjectMetdataSegmentsField, $cacheKey);
        } else {
            $hasObjectMetdataSegmentsField = RuntimeCache::load($cacheKey);
        }

        return $hasObjectMetdataSegmentsField;
    }

    /**
     * @param array $segments
     * @param array $removeSegments
     *
     * @return CustomerSegmentInterface[]|false
     */
    protected function removeSegmentsFromArray(&$segments, $removeSegments)
    {
        $removedSegments = Objects::removeObjectsFromArray($segments, $removeSegments);

        if ($removedSegments) {
            $removedSegments = $this->objectMetadataArrayToObjectArray($removedSegments);
        }

        return $removedSegments;
    }

    private function objectMetadataArrayToObjectArray(array $array)
    {
        foreach ($array as $key => $item) {
            $array[$key] = $item instanceof ObjectMetadata ? $item->getObject() : $item;
        }

        return $array;
    }

    /**
     * @param CustomerInterface $customer
     * @param bool $calculated
     *
     * @return \CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface[]|\Pimcore\Model\DataObject\Data\ObjectMetadata[]
     */
    public function getSegmentsDataFromCustomer(CustomerInterface $customer, $calculated = false)
    {
        if ($calculated) {
            return (array) $customer->getCalculatedSegments();
        }

        return (array) $customer->getManualSegments();
    }

    /**
     * @param CustomerInterface $customer
     * @param \CustomerManagementFrameworkBundle\Model\CustomerSegmentInterface[]|\Pimcore\Model\DataObject\Data\ObjectMetadata[] $segments
     * @param bool $calculated
     */
    protected function setSegmentsDataOfCustomer(CustomerInterface $customer, array $segments, $calculated = false)
    {
        if ($calculated) {
            $customer->setCalculatedSegments($segments);
        } else {
            $customer->setManualSegments($segments);
        }
    }

    /**
     * @inheritdoc
     */
    public function saveMergedSegments(CustomerInterface $customer)
    {
        if (isset($this->mergedSegmentsCustomerSaveQueue[$customer->getId()])) {
            $queueEntry = $this->mergedSegmentsCustomerSaveQueue[$customer->getId()];

            $this->customerSaveManager->saveDirty($customer);

            /**
             * @var Note $note
             */
            foreach ($queueEntry['notes'] as $note) {
                $note->save();
            }

            unset($this->mergedSegmentsCustomerSaveQueue[$customer->getId()]);

            $this->getLogger()->debug('merged segments saved for customer '.(string)$customer);
        }
    }

    /**
     * Remembers customers + notes which need to be saved by saveMergedSegments()
     *
     * @param CustomerInterface $customer
     * @param array $notes
     */
    protected function addToMergedSegmentsCustomerSaveQueue(CustomerInterface $customer, array $notes)
    {
        $this->mergedSegmentsCustomerSaveQueue[$customer->getId()] =
            isset($this->mergedSegmentsCustomerSaveQueue[$customer->getId()]) ?
                $this->mergedSegmentsCustomerSaveQueue[$customer->getId()] :
                [
                    'customer' => $customer,
                    'notes' => [],
                ];

        $this->mergedSegmentsCustomerSaveQueue[$customer->getId()]['notes'] = array_merge(
            $this->mergedSegmentsCustomerSaveQueue[$customer->getId()]['notes'],
            $notes
        );
    }

    /**
     * @param CustomerInterface $customer
     * @param array $segments
     * @param string $title
     * @param string $hintForNotes
     *
     * @return array
     */
    protected function createNotes(CustomerInterface $customer, $segments, $title, $hintForNotes)
    {
        $notes = [];

        $description = [];

        if ($hintForNotes) {
            $title .= ' ('.$hintForNotes.')';
        }

        $note = Notes::createNote($customer, 'cmf.SegmentManager', $title);
        $i = 0;
        foreach ($segments as $segment) {
            $i++;
            $note->addData('segment'.$i, 'object', $segment);
            $description[] = $segment;
        }
        $note->setDescription(implode(', ', $description));

        $notes[] = $note;

        return $notes;
    }
}
