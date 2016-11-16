<?php

namespace CustomerManagementFramework\Controller;

use CustomerManagementFramework\View\Helper\JsConfig;

class Admin extends \Pimcore\Controller\Action\Admin
{
    public function init()
    {
        parent::init();

        $this->initViewHelpers();
        $this->initJsConfig();
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

    /**
     * Init JS config
     */
    protected function initJsConfig()
    {
        /** @var JsConfig $jsConfig */
        $jsConfig = $this->view->getHelper('JsConfig');

        $jsConfig->add('debug', \Pimcore::inDebugMode());
        $jsConfig->add('_init', true);
    }
}
