<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 20.12.2016
 * Time: 14:01
 */

namespace CustomerManagementFrameworkBundle\DataTransformer\Zip2State;

use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

abstract class AbstractTransformer implements DataTransformerInterface
{
    protected $zipRegions = [];

    public function transform($data, $options = [])
    {

        foreach ($this->zipRegions as $state => $regions) {
            foreach ($regions as $region) {
                $from = $region[0];
                $to = $region[1];

                if (strlen($data) != strlen($from)) {
                    return null;
                }

                if ($data == $from) {
                    return $state;
                }

                if ($data >= $from && $data <= $to) {
                    return $state;
                }
            }
        }
    }
}