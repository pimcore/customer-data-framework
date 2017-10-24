<?php
/**
 * Created by PhpStorm.
 * User: kzumueller
 * Date: 23.10.2017
 * Time: 13:10
 */

namespace CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions;


use Pimcore\Db;

/**
 * @inheritdoc
 */
class DefaultStoredFunctions implements StoredFunctionsInterface {

    /**
     * @inheritdoc
     */
    public function retrieve(string $elementId, string $elementType): array {
         return explode(',', Db::get()->fetchColumn(sprintf('SELECT %s(%s)', static::STORED_FUNCTIONS_MAPPING[$elementType], $elementId)));
    }
}