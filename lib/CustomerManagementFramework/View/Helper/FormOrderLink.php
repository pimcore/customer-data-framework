<?php

namespace CustomerManagementFramework\View\Helper;

class FormOrderLink extends \Zend_View_Helper_Abstract
{
    public function formOrderLink($text, \Zend_Controller_Request_Http $request, $field)
    {
        $orderState = $this->getOrderState($request);

        $url   = $this->formOrderUrl($request, $field);
        $class = '';

        if (array_key_exists($field, $orderState) && in_array($orderState[$field], FormOrderParams::getValidDirections())) {
            $state = strtolower($orderState[$field]);
            $class = sprintf('table-sort--%s', $state);
        }

        $textLink = sprintf(
            '<a href="%s" class="table-sort %s">%s</a>',
            $url,
            $class,
            $text
        );

        return $textLink;
    }

    /**
     * @param \Zend_Controller_Request_Http $request
     * @param $field
     * @param array $orderState
     * @return string
     */
    public function formOrderUrl(\Zend_Controller_Request_Http $request, $field, array $orderState = null)
    {
        /** @var SelfUrl $selfUrlHelper */
        $selfUrlHelper = $this->view->getHelper('SelfUrl');

        /** @var FormQueryString $queryStringHelper */
        $queryStringHelper = $this->view->getHelper('FormQueryString');

        if (null === $orderState) {
            $orderState = $this->getOrderState($request);
        }

        $params = [
            'order'  => $this->toggleOrderState($orderState, $field),
            'filter' => $queryStringHelper->getFilterParams($request)
        ];

        return $queryStringHelper->addQueryStringToUrl(
            $selfUrlHelper->selfUrl($request, true, [], false),
            $params
        );
    }

    /**
     * @param \Zend_Controller_Request_Http $request
     * @return array
     */
    public function getOrderState(\Zend_Controller_Request_Http $request)
    {
        $order = $request->getParam('order', []);
        if (!is_array($order)) {
            $order = [];
        }

        return $order;
    }

    /**
     * Toggle order state for a field - if set and DESC -> change to ASC, if ASC set to none, if not set default to DESC
     *
     * @param array $order
     * @param $field
     * @return array
     */
    public function toggleOrderState(array $order, $field)
    {
        if (isset($order[$field])) {
            if ($order[$field] === \Zend_Db_Select::SQL_DESC) {
                $order[$field] = \Zend_Db_Select::SQL_ASC;
            } else if ($order[$field] === \Zend_Db_Select::SQL_ASC) {
                unset($order[$field]);
            }
        } else {
            $order[$field] = \Zend_Db_Select::SQL_DESC;
        }

        return $order;
    }
}
