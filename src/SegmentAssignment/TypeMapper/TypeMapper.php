<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 2017-09-18
 * Time: 4:13 PM
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\TypeMapper;


use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Object\AbstractObject;

class TypeMapper implements TypeMapperInterface {

    const TYPE_DOCUMENT = 'document';
    const TYPE_ASSET = 'asset';
    const TYPE_OBJECT = 'object';

    /**
     * @inheritdoc
     */
    public function getTypeStringByObject(ElementInterface $element): string {
        if($element instanceof Document) {
            return static::TYPE_DOCUMENT;
        }

        if($element instanceof Asset) {
            return static::TYPE_ASSET;
        }

        if($element instanceof AbstractObject) {
            return static::TYPE_OBJECT;
        }
    }
}