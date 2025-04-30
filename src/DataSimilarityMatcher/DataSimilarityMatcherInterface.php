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
