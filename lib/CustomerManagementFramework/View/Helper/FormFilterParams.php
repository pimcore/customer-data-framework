<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterParams extends \Zend_View_Helper_Abstract
{
    /**
     * Get filter params with values
     *
     * @param \Zend_Controller_Request_Http $request
     * @return array
     */
    public function formFilterParams(\Zend_Controller_Request_Http $request)
    {
        $result  = [];
        $filters = $request->getParam('filter');

        if (!is_array($filters)) {
            return $result;
        }

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
