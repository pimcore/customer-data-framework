<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFramework;

use CustomerManagementFramework\ActivityManager\DefaultActivityManager;

class Factory {


    private function __construct()
    {

    }

    /**
     * @return static
     */
    private static $instance;
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    /**
     * @return IActivityManager
     */
    private $activityManager;
    public function getActivityManager()
    {
        if(is_null($this->activityManager))
        {
            $this->activityManager = new DefaultActivityManager();
        }

        return $this->activityManager;
    }
}