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

interface DataSimilarityMatcherInterface
{
    /**
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return int|float
     */
    public function calculateSimilarity($value1, $value2);

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @param int|null $threshold
     *
     * @return bool
     */
    public function isSimilar($value1, $value2, $threshold = null);
}
