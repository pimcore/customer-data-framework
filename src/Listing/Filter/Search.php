<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

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
