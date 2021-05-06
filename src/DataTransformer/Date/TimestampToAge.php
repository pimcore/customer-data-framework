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

namespace CustomerManagementFrameworkBundle\DataTransformer\Date;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class TimestampToAge implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        $date = Carbon::createFromTimestamp(strtotime(date('Y-m-d', $data)));
        $today = new Carbon();

        return $today->diffInYears($date);
    }
}
