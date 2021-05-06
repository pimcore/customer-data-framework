<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
