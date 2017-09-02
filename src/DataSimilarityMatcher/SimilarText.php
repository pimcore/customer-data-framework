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

class SimilarText extends AbstractSimilarityMatcher
{
    protected $defaultTreshold = 80;

    public function calculateSimilarity($value1, $value2)
    {
        $percent = 0;
        similar_text($value1, $value2, $percent);

        return $percent;
    }
}
