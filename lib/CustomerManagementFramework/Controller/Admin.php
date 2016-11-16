<?php

namespace CustomerManagementFramework\Controller;

class Admin extends \Pimcore\Controller\Action\Admin
{
    public function init()
    {
        parent::init();

        $this->initViewHelpers();
    }

    /**
     * Init view helpers
     */
    protected function initViewHelpers()
    {
        /** @var \Zend_View $view */
        $view = $this->view;

        $view->addHelperPath(__DIR__ . '/../View/Helper', 'CustomerManagementFramework\\View\\Helper\\');
    }
}
