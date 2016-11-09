<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 14:14
 */

namespace CustomerManagementFramework\ActivityList;

use CustomerManagementFramework\ActivityList\DefaultMariaDbActivityList;

class Dao {

    /**
     * @var DefaultMariaDbActivityList
     */
    private $model;

    public function __construct(DefaultMariaDbActivityList $model) {
        $this->model = $model;
    }
}