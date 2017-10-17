<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
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
     * @param int $threshold
     *
     * @return bool
     */
    public function isSimilar($value1, $value2, $threshold = null);
}
