<?php

namespace CustomerManagementFramework\Listing\Filter;

use Pimcore\Model\Object\Listing as CoreListing;

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
