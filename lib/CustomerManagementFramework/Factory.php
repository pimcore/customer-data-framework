<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFramework;

use CustomerManagementFramework\ActivityManager\ActivityManagerInterface;
use CustomerManagementFramework\ActivityStore\ActivityStoreInterface;
use CustomerManagementFramework\ActivityView\ActivityViewInterface;
use CustomerManagementFramework\RESTApi\ExportInterface;
use CustomerManagementFramework\SegmentManager\SegmentManagerInterface;

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
     * @return ActivityManagerInterface
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
     * @return ActivityStoreInterface
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
     * @return SegmentManagerInterface
     */
    public function getSegmentManager()
    {
        if(is_null($this->segmentManager))
        {
            $this->segmentManager = \Pimcore::getDiContainer()->get('CustomerManagementFramework\SegmentManager');
        }

        return $this->segmentManager;
    }

    private $activityView;
    /**
     * @return ActivityViewInterface
     */
    public function getActivityView()
    {
        if(is_null($this->activityView))
        {
            $this->activityView = \Pimcore::getDiContainer()->get('CustomerManagementFramework\ActivityView');
        }

        return $this->activityView;
    }

    private $RESTApiExport;
    /**
     * @return ExportInterface
     */
    public function getRESTApiExport() {
        if(is_null($this->RESTApiExport))
        {
            $this->RESTApiExport = \Pimcore::getDiContainer()->get('CustomerManagementFramework\RESTApi\Export');
        }

        return $this->RESTApiExport;
    }
}