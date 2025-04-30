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

namespace CustomerManagementFrameworkBundle\DataSimilarityMatcher;

class BirthDate extends AbstractSimilarityMatcher
{
    protected $defaultThreshold = 50;

    /**
     * @param int $value1
     * @param int $value2
     *
     * @return int
     */
    public function calculateSimilarity($value1, $value2)
    {
        $d1 = date('d', $value1);
        $d2 = date('d', $value2);

        $m1 = date('m', $value1);
        $m2 = date('m', $value2);

        $y1 = date('y', $value1);
        $y2 = date('y', $value2);

        if ($d1 == $d2 && $m1 == $m2 && $y1 == $y2) {
            return 100;
        }

        if ($d1 == $d2 && $m1 == $m2) {
            return 66;
        }

        if ($d1 == $d2 && $y1 == $y2) {
            return 66;
        }

        if ($m1 == $m2 && $y1 == $y2) {
            return 66;
        }

        if (abs($y1 - $y2) <= 5) {
            return 50;
        }

        if (abs($y1 - $y2) <= 10) {
            return 25;
        }

        return 0;
    }
}
