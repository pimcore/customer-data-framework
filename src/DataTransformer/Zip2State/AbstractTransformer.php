<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
