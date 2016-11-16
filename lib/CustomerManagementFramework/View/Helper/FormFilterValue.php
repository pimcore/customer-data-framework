<?php

namespace CustomerManagementFramework\View\Helper;

class FormFilterValue extends \Zend_View_Helper_Abstract
{
    /**
     * Get filter value
     *
     * @param $name
     * @return string
     */
    public function formFilterValue($name)
    {
        if (isset($this->view->filters) && array_key_exists($name, $this->view->filters)) {
            $value = $this->view->filters[$name];
            $value = trim($value);
            $value = $this->view->escape($value);

            return $value;
        }

        return '';
    }
}
