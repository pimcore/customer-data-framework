<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 13:37
 */

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;
use CustomerManagementFramework\Factory;

abstract class AbstractTermSegmentBuilderDefinition extends \Pimcore\Model\Object\Concrete {

    public function definitionsToArray()
    {
        $result = [];

        if($terms = $this->getTerms()) {
            foreach($terms as $term) {
                $result[$term['term']->getData()] = isset($result[$term['term']->getData()]) ? $result[$term['term']->getData()] : [];
                $phrases = $term['phrases']->getData();
                if(sizeof($phrases)) {
                    $phrases = array_column($phrases, 0);
                    $result[$term['term']->getData()] = array_merge($result[$term['term']->getData()], $phrases);
                }
            }
        }

        return $result;
    }
}