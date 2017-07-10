<?php

namespace CustomerManagementFrameworkBundle\Listing\Filter;

class Equals extends AbstractFieldValue
{
    /**
     * @return string
     */
    protected function getComparisonOperator()
    {
        if ($this->inverse) {
            return '!=';
        } else {
            return '=';
        }
    }
}
