<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:46
 */

namespace CustomerManagementFramework\DataTransformer\Zip;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

class Se implements DataTransformerInterface
{
    public function transform($data)
    {
        preg_match("/\\b\\d{3} {0,1}\\d{2}\\b/", $data, $matches);

        $result = $data;
        if($match = $matches[0]) {
            if(strlen($match) == 5) {
                $result = substr($match, 0, 3) . ' ' . substr($match, 3);
            } else {
                $result = $match;
            }
        }

        return strtoupper($result);
    }

}