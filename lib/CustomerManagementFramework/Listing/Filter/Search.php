<?php

namespace CustomerManagementFramework\Listing\Filter;

use Pimcore\Model\Object\Listing as CoreListing;

class Search extends AbstractFieldValue
{
    /**
     * Wrap value in %
     *
     * @param string $value
     * @return string
     */
    protected function processValue($value)
    {
        return '%' . $value . '%';
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
