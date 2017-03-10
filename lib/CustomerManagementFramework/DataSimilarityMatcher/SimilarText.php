<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-03
 * Time: 12:22
 */

namespace CustomerManagementFramework\DataSimilarityMatcher;

class SimilarText implements DataSimilarityMatcherInterface {

    protected $defaultTreshold = 80;

    public function calculateSimilarity($value1, $value2)
    {
        $percent = 0;
        similar_text($value1, $value2, $percent);

        return $percent;
    }

    public function isSimilar($value1, $value2, $treshold = null)
    {
        $similarity = $this->calculateSimilarity($value1, $value2);

        $treshold = is_int($treshold) ? $treshold : $this->defaultTreshold;

        return $similarity >= $treshold;
    }

}