<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-03
 * Time: 12:21
 */

namespace CustomerManagementFramework\DataSimilarityMatcher;

interface DataSimilarityMatcherInterface {

    /**
     * @param $value1
     * @param $value2
     * @return int
     */
    public function calculateSimilarity($value1, $value2);

    /**
     * @param $value1
     * @param $value2
     * @param int $treshold
     * @return bool
     */
    public function isSimilar($value1, $value2, $treshold = null);
}