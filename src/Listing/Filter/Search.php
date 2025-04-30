<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
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
