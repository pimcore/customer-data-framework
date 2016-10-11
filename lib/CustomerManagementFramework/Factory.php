<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 07.10.2016
 * Time: 15:19
 */

namespace CustomerManagementFramework;

use CustomerManagementFramework\ActivityManager\DefaultActivityManager;
use CustomerManagementFramework\ActivityManager\IActivityManager;
use CustomerManagementFramework\Service\ElasticSearch;
use CustomerManagementFramework\Service\MariaDb;

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

    /**
     * @return ElasticSearch
     */
    private $elasticSearchService;
    public function getElasticSearchService()
    {
        if(is_null($this->elasticSearchService))
        {
            $this->elasticSearchService = new ElasticSearch();
        }

        return $this->elasticSearchService;
    }

    /**
     * @return MariaDb
     */
    private $mariaDbService;
    public function getMariaDbService()
    {
        if(is_null($this->mariaDbService))
        {
            $this->mariaDbService = new MariaDb();
        }

        return $this->mariaDbService;
    }
}