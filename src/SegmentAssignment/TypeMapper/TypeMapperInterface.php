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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Listing\AbstractListing;

/**
 * Interface TypeMapperInterface
 *
 * Interface for mapping types to element type strings used in database communication
 *
 * @package CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper
 */
interface TypeMapperInterface
{
    const TYPE_DOCUMENT = 'document';

    const TYPE_ASSET = 'asset';

    const TYPE_OBJECT = 'object';

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided element
     *
     *
     */
    public function getTypeStringByObject(ElementInterface $element): string;

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided sub type
     *
     *
     */
    public function getTypeStringBySubType(string $subType): string;

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') based on the type of Listing provided
     *
     *
     */
    public function getTypeStringByListing(AbstractListing $listing): string;
}
