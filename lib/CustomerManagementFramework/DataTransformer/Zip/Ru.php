<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:46
 */

namespace CustomerManagementFramework\DataTransformer\Zip;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

class Ru implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        preg_match("/\\b\\d{6}\\b/", $data, $matches);

        if($match = $matches[0]) {
            return $match;
        }

        return $data;
    }

}