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
     * @param ElementInterface $element
     *
     * @return string
     */
    public function getTypeStringByObject(ElementInterface $element): string;

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') for the provided sub type
     *
     * @param string $subType
     *
     * @return string
     */
    public function getTypeStringBySubType(string $subType): string;

    /**
     * returns a type string (e.g. 'document'|'asset'|'object') based on the type of Listing provided
     *
     * @param AbstractListing $listing
     *
     * @return string
     */
    public function getTypeStringByListing(AbstractListing $listing): string;
}
