<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFramework;

use CustomerManagementFramework\ActivityManager\IActivityManager;
use CustomerManagementFramework\ActivityStore\IActivityStore;
use CustomerManagementFramework\RESTApi\IExport;
use CustomerManagementFramework\SegmentManager\ISegmentManager;

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

    private $activityManager;
    /**
     * @return IActivityManager
     */
    public function getActivityManager()
    {
        if(is_null($this->activityManager))
        {
            $this->activityManager = \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityManager');
        }

        return $this->activityManager;
    }


    private $activityStore;
    /**
     * @return IActivityStore
     */
    public function getActivityStore()
    {
        if(is_null($this->activityStore))
        {
            $this->activityStore = \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityStore');
        }

        return $this->activityStore;
    }

    private $segmentManager;
    /**
     * @return ISegmentManager
     */
    public function getSegmentManager()
    {
        if(is_null($this->segmentManager))
        {
            $this->segmentManager = \Pimcore::getDiContainer()->get('CustomerManagementFramework\SegmentManager');
        }

        return $this->segmentManager;
    }

    private $RESTApiExport;
    /**
     * @return IExport
     */
    public function getRESTApiExport() {
        if(is_null($this->RESTApiExport))
        {
            $this->RESTApiExport = \Pimcore::getDiContainer()->get('CustomerManagementFramework\RESTApi\Export');
        }

        return $this->RESTApiExport;
    }
}