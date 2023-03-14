<?php

declare(strict_types=1);

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

namespace CustomerManagementFrameworkBundle\Traits;

use Doctrine\DBAL\Connection;
use Pimcore\Cache;
use Pimcore\Cache\RuntimeCache;

trait PrimaryKeyTrait
{
    private string $cacheKey = 'system_resource_primary_columns_';

    public function getPrimaryKey(Connection $db, string $table, bool $cache = true): array
    {
        $cacheKey = $this->cacheKey . $table;

        if (RuntimeCache::isRegistered($cacheKey)) {
            $primaryKeyColumns = RuntimeCache::get($cacheKey);
        } else {
            $primaryKeyColumns = Cache::load($cacheKey);
            if (!$primaryKeyColumns || !$cache) {
                $primaryKeyColumns = [];
                $data = $db->fetchAllAssociative('SHOW COLUMNS FROM ' . $table . ' WHERE `Key` LIKE \'PRI\'');
                foreach ($data as $d) {
                    $primaryKeyColumns[] = $d['Field'];
                }
                Cache::save($primaryKeyColumns, $cacheKey, ['system', 'resource'], null, 997);
            }

            RuntimeCache::set($cacheKey, $primaryKeyColumns);
        }

        return $primaryKeyColumns;
    }
}
