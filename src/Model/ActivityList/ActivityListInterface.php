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

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use Pimcore\Model\Paginator\PaginateListingInterface;

interface ActivityListInterface extends PaginateListingInterface
{
    public function setCondition(string $condition, float | array | bool | int | string|null $conditionVariables = null): static;
}
