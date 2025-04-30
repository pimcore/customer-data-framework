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

namespace CustomerManagementFrameworkBundle\DataTransformer\Date;

use Carbon\Carbon;
use CustomerManagementFrameworkBundle\DataTransformer\DataTransformerInterface;

class TimestampToAge implements DataTransformerInterface
{
    public function transform($data, $options = [])
    {
        $date = Carbon::createFromTimestamp(strtotime(date('Y-m-d', $data)), date_default_timezone_get());
        $today = new Carbon();

        return $today->diffInYears($date);
    }
}
