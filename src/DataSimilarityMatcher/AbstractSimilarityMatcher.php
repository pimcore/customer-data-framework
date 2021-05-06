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

abstract class AbstractSimilarityMatcher implements DataSimilarityMatcherInterface
{
    protected $defaultThreshold = 90;

    public function isSimilar($value1, $value2, $threshold = null)
    {
        $similarity = $this->calculateSimilarity($value1, $value2);

        $threshold = is_int($threshold) ? $threshold : $this->defaultThreshold;

        return $similarity >= $threshold;
    }
}
