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

namespace CustomerManagementFrameworkBundle\SegmentAssignment\StoredFunctions;

use Pimcore\Db;

class DefaultStoredFunctions implements StoredFunctionsInterface
{
    public function retrieve(string $elementId, string $elementType): array
    {
        $storedFunction = static::STORED_FUNCTIONS_MAPPING[$elementType];

        return explode(',', Db::get()->fetchOne("SELECT $storedFunction(:elementId)", ['elementId' => $elementId ?: 0]));
    }
}
