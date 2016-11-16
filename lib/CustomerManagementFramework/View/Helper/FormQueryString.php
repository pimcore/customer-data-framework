<?php

namespace CustomerManagementFramework\View\Helper;

class FormQueryString extends \Zend_View_Helper_Abstract
{
    /**
     * Add filter query string to URL
     *
     * @param \Zend_Controller_Request_Http $request
     * @param $url
     * @param bool $includeOrder
     * @param bool $includeFilters
     * @return string
     */
    public function formQueryString(\Zend_Controller_Request_Http $request, $url, $includeOrder = true, $includeFilters = true)
    {
        return $this->addQueryStringToUrl($url, $this->getQueryParams($request, $includeOrder, $includeFilters));
    }

    /**
     * Get filter query params from request
     *
     * @param \Zend_Controller_Request_Http $request
     * @param bool $includeOrder
     * @param bool $includeFilters
     * @return array
     */
    public function getQueryParams(\Zend_Controller_Request_Http $request, $includeOrder = true, $includeFilters = true)
    {
        $params = [];

        if ($includeOrder) {
            $params['order'] = $this->getOrderParams($request);
        }

        if ($includeFilters) {
            $params['filter'] = $this->getFilterParams($request);
        }

        return $params;
    }

    /**
     * @param \Zend_Controller_Request_Http $request
     * @return array
     */
    public function getFilterParams(\Zend_Controller_Request_Http $request)
    {
        /** @var FormFilterParams $helper */
        $helper = $this->view->getHelper('FormFilterParams');
        $params = $helper->formFilterParams($request);

        return $params;
    }

    /**
     * @param \Zend_Controller_Request_Http $request
     * @return array
     */
    public function getOrderParams(\Zend_Controller_Request_Http $request)
    {
        /** @var FormOrderParams $helper */
        $helper = $this->view->getHelper('FormOrderParams');
        $params = $helper->formOrderParams($request);

        return $params;
    }

    /**
     * Add filter query string to URL
     *
     * @param $url
     * @param array $params
     * @return string
     */
    public function addQueryStringToUrl($url, array $params = [])
    {
        $queryParams = [];
        foreach ($params as $key => $values) {
            if (count($values) > 0) {
                $queryParams[$key] = $values;
            }
        }

        if (count($queryParams) > 0) {
            $queryString = http_build_query($queryParams);

            if (strlen($queryString) > 0) {
                if (false === strpos($url, '?')) {
                    $url = $url . '?' . $queryString;
                } else {
                    $url = $url . '&' . $queryString;
                }
            }
        }

        return $url;
    }
}
