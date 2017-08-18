<?php

namespace CustomerManagementFrameworkBundle\Listing\Filter;

class Search extends AbstractFieldValue
{
    /**
     * Wrap value in %
     *
     * @param string $value
     *
     * @return string
     */
    protected function processValue($value)
    {
        return '%'.$value.'%';
    }

    /**
     * @return string
     */
    protected function getComparisonOperator()
    {
        if ($this->inverse) {
            return 'NOT LIKE';
        } else {
            return 'LIKE';
        }
    }
}
