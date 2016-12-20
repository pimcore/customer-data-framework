<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 20.12.2016
 * Time: 15:41
 */

namespace CustomerManagementFramework\DataTransformer\Date;

use Carbon\Carbon;
use CustomerManagementFramework\DataTransformer\DataTransformerInterface;

class TimestampToAge implements DataTransformerInterface {

    public function transform($data)
    {
        $date = Carbon::createFromTimestamp(strtotime(date('Y-m-d', $data)));
        $today = new Carbon();
        return $today->diffInYears($date);
    }

}