<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-02
 * Time: 17:39
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex;

class Street extends Simplify
{

    public function transform($data, $options = [])
    {
        $data = parent::transform($data, $options);
        $data = str_replace(['strasse'], ['str.'], $data);

        return preg_replace('/\str$/', 'str.', $data);
    }

}