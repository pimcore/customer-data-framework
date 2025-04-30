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
