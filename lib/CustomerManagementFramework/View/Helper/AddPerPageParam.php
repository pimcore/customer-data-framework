<?php

namespace CustomerManagementFramework\View\Helper;

class AddPerPageParam extends \Zend_View_Helper_Abstract
{
    /**
     * Add perPage param if set and not the default value
     *
     * @param \Zend_Controller_Request_Http $request
     * @param array $params
     * @param int $defaultPageSize
     * @return array
     */
    public function addPerPageParam(\Zend_Controller_Request_Http $request, array $params = [], $defaultPageSize = null)
    {
        if (null === $defaultPageSize) {
            $defaultPageSize = $this->view->defaultPageSize();
        } else {
            $defaultPageSize = (int)$defaultPageSize;
        }

        $perPageParam = (int)$request->getParam('perPage', 0);
        if ($perPageParam <= 0) {
            return $params;
        }

        if ($perPageParam !== $defaultPageSize) {
            $params['perPage'] = $perPageParam;
        }

        return $params;
    }
}
