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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions;

use Pimcore\Db;

/**
 * @inheritdoc
 */
class DefaultStoredFunctions implements StoredFunctionsInterface
{
    /**
     * @inheritdoc
     */
    public function retrieve(string $elementId, string $elementType): array
    {
        $storedFunction = static::STORED_FUNCTIONS_MAPPING[$elementType];

        return explode(',', Db::get()->fetchOne("SELECT $storedFunction(:elementId)", ['elementId' => $elementId ?: 0]));
    }
}
