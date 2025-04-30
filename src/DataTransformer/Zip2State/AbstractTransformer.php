<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
                $from = $region[0] ?? null;
                $to = $region[1] ?? null;

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
