<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
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
