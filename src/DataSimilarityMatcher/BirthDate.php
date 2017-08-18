<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 2017-03-03
 * Time: 12:22
 */

namespace CustomerManagementFrameworkBundle\DataSimilarityMatcher;

class BirthDate implements DataSimilarityMatcherInterface
{
    protected $defaultTreshold = 50;

    /**
     * @param int $value1
     * @param int $value2
     *
     * @return int
     */
    public function calculateSimilarity($value1, $value2)
    {
        $d1 = date('d', $value1);
        $d2 = date('d', $value2);

        $m1 = date('m', $value1);
        $m2 = date('m', $value2);

        $y1 = date('y', $value1);
        $y2 = date('y', $value2);

        if ($d1 == $d2 && $m1 == $m2 && $y1 == $y2) {
            return 100;
        }

        if ($d1 == $d2 && $m1 == $m2) {
            return 66;
        }

        if ($d1 == $d2 && $y1 == $y2) {
            return 66;
        }

        if ($m1 == $m2 && $y1 == $y2) {
            return 66;
        }

        if (abs($y1 - $y2) <= 5) {
            return 50;
        }

        if (abs($y1 - $y2) <= 10) {
            return 25;
        }

        return 0;
    }

    public function isSimilar($value1, $value2, $treshold = null)
    {
        $similarity = $this->calculateSimilarity($value1, $value2);

        $treshold = is_int($treshold) ? $treshold : $this->defaultTreshold;

        return $similarity >= $treshold;
    }
}
