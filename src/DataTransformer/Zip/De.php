<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:46
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Zip;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class De implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match("/\\b\\d{4,5}\\b/", $data, $matches);

        if ($match = $matches[0]) {
            if (strlen($match) == 4 && !(strpos($match, '0') === 0)) {
                return '0'.$match;
            }

            return $match;
        }

        return $data;
    }

}