<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 25/01/2018
 * Time: 11:08
 */

namespace CustomerManagementFrameworkBundle\PathFormatter;

use Pimcore\Model\DataObject\CustomerSegment;
use Pimcore\Model\DataObject\CustomerSegmentGroup;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\Concrete;

class CustomerSegmentFormatter {
    /**
     * @param $result array containing the nice path info. Modify it or leave it as it is. Pass it out afterwards!
     * @param ElementInterface $source the source object
     * @param $targets list of nodes describing the target elements
     * @param $params optional parameters. may contain additional context information in the future. to be defined.
     * @return mixed list of display names.
     */
    public static function formatPath($result, ElementInterface $source, $targets, $params) {
        /** @var  $fd Data */
        $fd = $params["fd"];
        $context = $params["context"];

        foreach ($targets as $key => $item) {
            $newPath = $item["path"] .  " - " . time();
            if ($context["language"]) {
                $newPath .= " " . $context["language"];
            }

            if ($item["type"] == "object") {
                $targetObject = Concrete::getById($item["id"]);
                if ($targetObject instanceof CustomerSegment) {
                    $newPath = '<strong>' . $targetObject->getName() . '</strong>';

                    /**
                     * @var CustomerSegmentGroup $group
                     */
                    if($group = $targetObject->getGroup()) {
                        $newPath .= ' [' . $group->getName() . ']';
                    }
                }
            }

            // don't forget to use the same key, otherwise the matching doesn't work
            $result[$key]= $newPath;
        }
        return $result;
    }
}