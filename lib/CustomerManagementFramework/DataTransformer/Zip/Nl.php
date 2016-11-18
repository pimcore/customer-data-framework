<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 17.11.2016
 * Time: 11:46
 */

namespace CustomerManagementFramework\DataTransformer\Zip;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

class Nl implements DataTransformerInterface
{
    public function transform($data)
    {
        preg_match("/\\d{4} {0,1}\\w{2}/", $data, $matches);

        $result = $data;
        if($match = $matches[0]) {
            if(strlen($match) == 6) {
                $result = substr($match, 0, 4) . ' ' . substr($match, 4);
            } else {
                $result = $match;
            }
        }

        return strtoupper($result);
    }

}