<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-18
 * Time: 4:07 PM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper;

use Pimcore\Model\Element\ElementInterface;

/**
 * Interface TypeMapperInterface
 *
 * Interface for mapping types to element type strings used in database communication
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper
 */
interface TypeMapperInterface {

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided element
     *
     * @param ElementInterface $element
     * @return string
     */
    public function getTypeStringByObject(ElementInterface $element): string;

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided sub type
     *
     * @param string $subType
     * @return string
     */
    public function getTypeStringBySubType(string $subType): string;
}