<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 14.11.2016
 * Time: 11:28
 */
namespace CustomerManagementFrameworkBundle\Helper;

class Json {

    /**
     * @param string $json
     *
     * @return string
     */
    public static function cleanUpJson($json) {
        $search = array("\n", "\r", "\u", "\t", "\f", "\b", "/", '"');
        $replace = array("\\n", "\\r", "\\u", "\\t", "\\f", "\\b", "\/", "\"");
        return str_replace($search, $replace, $json);
    }
}