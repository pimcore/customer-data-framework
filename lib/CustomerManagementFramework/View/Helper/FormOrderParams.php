<?php

namespace CustomerManagementFramework\View\Helper;

class FormOrderParams extends \Zend_View_Helper_Abstract
{
    /**
     * Get order params
     *
     * @param \Zend_Controller_Request_Http $request
     * @return array
     */
    public function formOrderParams(\Zend_Controller_Request_Http $request)
    {
        $result = [];
        $order  = $request->getParam('order');

        if (!is_array($order)) {
            return $result;
        }

        $validDirections = static::getValidDirections();
        foreach ($order as $field => $direction) {
            if (in_array($direction, $validDirections)) {
                $result[$field] = $direction;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getValidDirections()
    {
        return [
            \Zend_Db_Select::SQL_ASC,
            \Zend_Db_Select::SQL_DESC
        ];
    }
}
