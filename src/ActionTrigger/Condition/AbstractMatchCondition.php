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

namespace CustomerManagementFrameworkBundle\ActionTrigger\Condition;

abstract class AbstractMatchCondition extends AbstractCondition
{
    protected function matchCondition(int $segmentCount, string $operator, int $value): bool
    {
        switch ($operator) {
            case '%':
                return $segmentCount % $value === 0;

            case '=':
                return $segmentCount === $value;

            case '>':
                return $segmentCount > $value;

            case '>=':
                return $segmentCount >= $value;

            case '<':
                return $segmentCount < $value;

            case '<=':
                return $segmentCount <= $value;
        }

        throw new \InvalidArgumentException(sprintf('Unsupported operator "%s"', $operator));
    }
}
