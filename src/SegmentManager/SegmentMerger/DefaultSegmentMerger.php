<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\SegmentManager\SegmentMerger;

use CustomerManagementFrameworkBundle\Helper\Notes;
use CustomerManagementFrameworkBundle\Helper\Objects;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use CustomerManagementFrameworkBundle\Traits\LoggerAware;
use Pimcore\Model\Element\Note;

class DefaultSegmentMerger implements SegmentMergerInterface
{
    use LoggerAware;

    protected $mergedSegmentsCustomerSaveQueue;

    /**
     * @inheritdoc
     */
    public function mergeSegments(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $hintForNotes = null
    ) {
        $addCalculatedSegments = [];
        foreach ($addSegments as $segment) {
            if ($segment->getCalculated()) {
                $addCalculatedSegments[] = $segment;
            }
        }
        $deleteCalculatedSegments = [];
        foreach ($deleteSegments as $segment) {
            if ($segment->getCalculated()) {
                $deleteCalculatedSegments[] = $segment;
            }
        }

        if (sizeof($addCalculatedSegments) || sizeof($deleteCalculatedSegments)) {
            $this->mergeSegmentsHelper(
                $customer,
                $addCalculatedSegments,
                $deleteCalculatedSegments,
                true,
                $hintForNotes
            );
        }

        $addManualSegments = [];
        foreach ($addSegments as $segment) {
            if (!$segment->getCalculated()) {
                $addManualSegments[] = $segment;
            }
        }
        $deleteManualSegments = [];
        foreach ($deleteSegments as $segment) {
            if (!$segment->getCalculated()) {
                $deleteManualSegments[] = $segment;
            }
        }

        if (sizeof($addManualSegments) || sizeof($deleteManualSegments)) {
            $this->mergeSegmentsHelper($customer, $addManualSegments, $deleteManualSegments, false, $hintForNotes);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @param array $addSegments
     * @param array $deleteSegments
     * @param bool $calculated
     * @param                   $hintForNotes
     */
    protected function mergeSegmentsHelper(
        CustomerInterface $customer,
        array $addSegments,
        array $deleteSegments = [],
        $calculated = false,
        $hintForNotes
    ) {
        $currentSegments = $calculated ? (array)$customer->getCalculatedSegments(
        ) : (array)$customer->getManualSegments();

        $saveNeeded = false;
        if ($addedSegments = Objects::addObjectsToArray($currentSegments, $addSegments)) {
            $saveNeeded = true;
        }

        if ($removedSegments = Objects::removeObjectsFromArray($currentSegments, $deleteSegments)) {
            $saveNeeded = true;
        }

        if ($saveNeeded) {
            if ($calculated) {
                $customer->setCalculatedSegments($currentSegments);
            } else {
                $customer->setManualSegments($currentSegments);
            }

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
     * @inheritdoc
     */
    public function saveMergedSegments(CustomerInterface $customer)
    {
        if (isset($this->mergedSegmentsCustomerSaveQueue[$customer->getId()])) {
            $queueEntry = $this->mergedSegmentsCustomerSaveQueue[$customer->getId()];

            \Pimcore::getContainer()->get('cmf.customer_save_manager')->saveDirty($customer);

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
     * @param $segments
     * @param $title
     * @param $hintForNotes
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
