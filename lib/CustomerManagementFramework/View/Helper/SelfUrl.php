<?php

namespace CustomerManagementFramework\View\Helper;

class SelfUrl extends \Zend_View_Helper_Abstract
{
    /**
     * Return URL to current action without any params
     *
     * @param \Zend_Controller_Request_Http $request
     * @param bool $reset
     * @param array $params
     * @param bool $includeModule
     * @return mixed
     */
    public function selfUrl(\Zend_Controller_Request_Http $request, $reset = true, array $params = [], $includeModule = true)
    {
        if ($includeModule) {
            $params['module'] = $request->module;
        }

        foreach(['controller', 'action'] as $key) {
            $params[$key] = $request->$key;
        }

        return $this->view->url($params, null, $reset);
    }
}
