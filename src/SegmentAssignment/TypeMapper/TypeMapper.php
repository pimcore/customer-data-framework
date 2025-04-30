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

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Listing\Concrete;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Listing\AbstractListing;

class TypeMapper implements TypeMapperInterface
{
    public function getTypeStringByObject(ElementInterface $element): string
    {
        if ($element instanceof Document) {
            return static::TYPE_DOCUMENT;
        }

        if ($element instanceof Asset) {
            return static::TYPE_ASSET;
        }

        if ($element instanceof AbstractObject) {
            return static::TYPE_OBJECT;
        }

        return '';
    }

    public function getTypeStringBySubType(string $subType): string
    {
        if (in_array($subType, Document::getTypes())) {
            return static::TYPE_DOCUMENT;
        }

        if (in_array($subType, Asset::getTypes())) {
            return static::TYPE_ASSET;
        }

        if (in_array($subType, AbstractObject::getTypes())) {
            return static::TYPE_OBJECT;
        }

        return '';
    }

    public function getTypeStringByListing(AbstractListing $listing): string
    {
        if ($listing instanceof Document\Listing) {
            return static::TYPE_DOCUMENT;
        }

        if ($listing instanceof Asset\Listing) {
            return static::TYPE_ASSET;
        }

        if ($listing instanceof Concrete) {
            return static::TYPE_OBJECT;
        }

        return '';
    }
}
