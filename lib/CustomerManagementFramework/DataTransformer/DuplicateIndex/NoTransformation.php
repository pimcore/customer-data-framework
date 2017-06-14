<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-02
 * Time: 17:39
 */

namespace CustomerManagementFramework\DataTransformer\DuplicateIndex;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

class NoTransformation implements DataTransformerInterface {

    public function transform($data, $options = [])
    {
        return $data;
    }

}