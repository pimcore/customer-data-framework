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

class ShoeSize extends AbstractSimilarityMatcher
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
        $distance = abs($value1 - $value2);

        if ($distance == 0) {
            return 100;
        }

        if ($distance == 1) {
            return 75;
        }

        if ($distance == 2) {
            return 50;
        }

        if ($distance == 3) {
            return 25;
        }

        return 0;
    }
}
