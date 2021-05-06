<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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
