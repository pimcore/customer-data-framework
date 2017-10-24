<?php

namespace CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions;

use CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper\TypeMapperInterface;

/**
 * Interface for interacting with the stored db functions installed with the bundle
 * They all move up the respective element tree until the root is reached or inheritance is broken
 * and concatenate the assigned segments' ids
 *
 * User: kzumueller
 * Date: 23.10.2017
 * Time: 12:48
 */
interface StoredFunctionsInterface {

    const STORED_FUNCTION_DOCUMENT = 'PLUGIN_CMF_COLLECT_DOCUMENT_SEGMENT_ASSIGNMENTS';
    const STORED_FUNCTION_ASSET = 'PLUGIN_CMF_COLLECT_ASSET_SEGMENT_ASSIGNMENTS';
    const STORED_FUNCTION_OBJECT = 'PLUGIN_CMF_COLLECT_OBJECT_SEGMENT_ASSIGNMENTS';

    /**
     * maps the stored functions to an element type, since they are type specific
     */
    const STORED_FUNCTIONS_MAPPING = [
        TypeMapperInterface::TYPE_DOCUMENT => self::STORED_FUNCTION_DOCUMENT,
        TypeMapperInterface::TYPE_ASSET => self::STORED_FUNCTION_ASSET,
        TypeMapperInterface::TYPE_OBJECT => self::STORED_FUNCTION_OBJECT
    ];

    /**
     * retrieves an array of segment ids assigned to the given $elementId of $elementType
     * and all those it inherits along the element tree
     *
     * @param string $elementId
     * @param string $elementType
     * @return string[]
     */
    public function retrieve(string $elementId, string $elementType): array;
}