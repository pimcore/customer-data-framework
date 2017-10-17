<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;

class TypeMapper implements TypeMapperInterface
{
    const TYPE_DOCUMENT = 'document';
    const TYPE_ASSET = 'asset';
    const TYPE_OBJECT = 'object';

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getTypeStringBySubType(string $subType): string
    {
        if (in_array($subType, Document::$types)) {
            return static::TYPE_DOCUMENT;
        }

        if (in_array($subType, Asset::$types)) {
            return static::TYPE_ASSET;
        }

        if (in_array($subType, AbstractObject::$types)) {
            return static::TYPE_OBJECT;
        }

        return '';
    }
}
