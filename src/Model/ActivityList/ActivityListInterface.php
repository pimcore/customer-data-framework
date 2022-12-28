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

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use Pimcore\Model\Paginator\PaginateListingInterface;
use Pimcore\Version;

/**
 * @TODO remove when remove support for Pimcore 10
 */
if (Version::MAJOR_VERSION >= 11) {
    interface ActivityListInterface extends PaginateListingInterface
    {
        public function setCondition(string $condition, float | array | bool | int | string $conditionVariables = null): static;
    }
} else {
    interface ActivityListInterface extends PaginateListingInterface
    {
        public function setCondition($condition, $conditionVariables = null);
    }
}
