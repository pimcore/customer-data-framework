<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 25.10.2016
 * Time: 09:41
 */

namespace CustomerManagementFramework\Filter;

class ExportCustomersFilterParams {


    /**
     * @var boolean
     */
    private $includeActivities;

    /**
     * @return boolean
     */
    public function getIncludeActivities()
    {
        return $this->includeActivities;
    }

    /**
     * @param boolean $includeActivities
     */
    public function setIncludeActivities($includeActivities)
    {
        $this->includeActivities = $includeActivities;
    }


}