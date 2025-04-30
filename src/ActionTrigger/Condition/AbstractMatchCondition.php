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
