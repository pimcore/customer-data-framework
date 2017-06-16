<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 25.10.2016
 * Time: 09:41
 */

namespace CustomerManagementFrameworkBundle\Filter;

class ExportCustomersFilterParams {


    /**
     * @var boolean
     */
    private $includeActivities;

    /**
     * @var array
     */
    private $allParams;

    /**
     * @var int[]
     */
    private $segments;

    /**
     * @param \Zend_Controller_Request_Http $request
     * @return static
     */
    public static function fromRequest(\Zend_Controller_Request_Http $request)
    {
        $params = new static();
        $params->setIncludeActivities($request->getParam('includeActivities') == 'true' ? true : false);
        $params->setSegments($request->getParam('segments'));
        $params->setAllParams($request->getParams());

        return $params;
    }

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

    /**
     * @return array
     */
    public function getAllParams()
    {
        return $this->allParams;
    }

    /**
     * @param array $allParams
     */
    public function setAllParams($allParams)
    {
        $this->allParams = $allParams;
    }

    /**
     * @return int|\int[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @param int|\int[] $segments
     */
    public function setSegments($segments)
    {
        if(is_array($segments)) {
            $this->segments = $segments;
        } elseif($segments) {
            $this->segments = [$segments];
        } else {
            $this->segments = [];
        }

    }

}
