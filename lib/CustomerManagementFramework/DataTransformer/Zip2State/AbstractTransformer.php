<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 20.12.2016
 * Time: 14:01
 */

namespace CustomerManagementFramework\DataTransformer\Zip2State;

use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

abstract class AbstractTransformer implements DataTransformerInterface
{
    protected $zipRegions = [ ];

    public function transform($zip)
    {

        foreach($this->zipRegions as $state => $regions) {
            foreach($regions as $region) {
                $from = $region[0];
                $to = $region[1];

                if(strlen($zip) != strlen($from)) {
                    return null;
                }

                if($zip == $from) {
                    return $state;
                }

                if($zip >= $from && $zip <= $to) {
                    return $state;
                }
            }
        }
    }
}