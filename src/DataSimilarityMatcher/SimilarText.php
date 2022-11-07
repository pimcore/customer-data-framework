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

class SimilarText extends AbstractSimilarityMatcher
{
    protected $defaultThreshold = 80;

    /**
     * @param string $value1
     * @param string $value2
     *
     * @return float
     */
    public function calculateSimilarity($value1, $value2)
    {
        similar_text($value1, $value2, $percent);

        return $percent;
    }
}
