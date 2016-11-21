<?php

namespace CustomerManagementFramework\View\Helper;

class DefaultPageSize extends \Zend_View_Helper_Abstract
{
    const DEFAULT_PAGE_SIZE = 25;

    /**
     * @return int
     */
    public function defaultPageSize()
    {
        if (!isset($this->view->defaultPageSize) || (int)$this->view->defaultPageSize < 0) {
            return static::DEFAULT_PAGE_SIZE;
        }

        return (int)$this->view->defaultPageSize;
    }
}
