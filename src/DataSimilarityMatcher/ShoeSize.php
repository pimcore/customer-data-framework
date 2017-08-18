<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\DataSimilarityMatcher;

class ShoeSize implements DataSimilarityMatcherInterface
{
    protected $defaultTreshold = 50;

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

    public function isSimilar($value1, $value2, $treshold = null)
    {
        $similarity = $this->calculateSimilarity($value1, $value2);

        $treshold = is_int($treshold) ? $treshold : $this->defaultTreshold;

        return $similarity >= $treshold;
    }
}
