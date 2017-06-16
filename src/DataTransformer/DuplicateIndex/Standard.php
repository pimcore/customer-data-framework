<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-02
 * Time: 17:39
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\DuplicateIndex;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class Standard implements DataTransformerInterface {

    public function transform($data, $options = []) {
        if( $data instanceof \DateTime ) {
            $data = $data->format( \DateTime::ISO8601 );
        }

        return trim(strtolower(str_replace('  ', ' ', $data)));
    }

}