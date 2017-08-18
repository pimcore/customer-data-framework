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

interface DataSimilarityMatcherInterface
{
    /**
     * @param $value1
     * @param $value2
     *
     * @return int
     */
    public function calculateSimilarity($value1, $value2);

    /**
     * @param $value1
     * @param $value2
     * @param int $treshold
     *
     * @return bool
     */
    public function isSimilar($value1, $value2, $treshold = null);
}
