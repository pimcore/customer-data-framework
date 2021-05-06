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
