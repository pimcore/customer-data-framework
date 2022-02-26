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

namespace CustomerManagementFrameworkBundle\Model;

use Pimcore\Model\DataObject\CustomerSegmentGroup;

/**
 * @method \Pimcore\Model\DataObject\Data\BlockElement[][]|null getTerms()
 */
abstract class AbstractTermSegmentBuilderDefinition extends \Pimcore\Model\DataObject\Concrete
{
    /**
     * @return array
     */
    public function definitionsToArray()
    {
        $result = [];

        if ($terms = $this->getTerms()) {
            foreach ($terms as $term) {
                $result[$term['term']->getData()] = isset(
                    $result[$term['term']->getData()]
                ) ? $result[$term['term']->getData()] : [];
                $phrases = $term['phrases']->getData();
                if (sizeof($phrases)) {
                    $phrases = array_column($phrases, 0);
                    $result[$term['term']->getData()] = array_merge($result[$term['term']->getData()], $phrases);
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAllTerms()
    {
        $result = [];
        foreach ($this->definitionsToArray() as $term => $phrases) {
            $result[] = $term;
        }

        return array_unique($result);
    }

    /**
     * @return array
     */
    public function getAllPhrases()
    {
        $result = [];
        foreach ($this->definitionsToArray() as $phrases) {
            $result = array_merge($result, (array)$phrases);
        }

        return array_unique($result);
    }

    /**
     * @param array $phrases
     *
     * @return array
     */
    public function getMatchingPhrases(array $phrases)
    {
        $allPhrases = $this->getAllPhrases();

        $result = [];

        foreach ($phrases as $term) {
            foreach ($allPhrases as $_term) {
                if ($term == $_term) {
                    $result[] = $term;
                    break;
                }

                if (@preg_match($_term, $term)) {
                    $result[] = $term;
                    break;
                }
            }
        }

        $result = array_unique($result);

        return $result;
    }

    /**
     * Adds/deletes CustomerSegment objects within given $customerSegmentGroup depending on defined terms within this TermSegmentBuilderDefinition.
     *
     * @param CustomerSegmentGroup $customerSegmentGroup
     *
     * @return void;
     */
    public function updateCustomerSegments(CustomerSegmentGroup $customerSegmentGroup)
    {
        $terms = $this->getAllTerms();
        $currentSegments = \Pimcore::getContainer()->get('cmf.segment_manager')->getSegmentsFromSegmentGroup(
            $customerSegmentGroup
        );

        $updatedSegments = [];
        foreach ($terms as $term) {
            $updatedSegments[] = \Pimcore::getContainer()->get('cmf.segment_manager')->createSegment(
                $term,
                $customerSegmentGroup,
                $term,
                $customerSegmentGroup->getCalculated()
            );
        }

        // remove all entries from $updaedSegments from $currentSegments
        foreach ($currentSegments as $key => $currentSegment) {
            foreach ($updatedSegments as $updatedSegment) {
                if ($currentSegment->getId() == $updatedSegment->getId()) {
                    unset($currentSegments[$key]);
                    break;
                }
            }
        }

        // delete remaining entries from $currentSegments as they are not relevant anymore
        foreach ($currentSegments as $currentSegment) {
            $currentSegment->delete();
        }
    }
}
