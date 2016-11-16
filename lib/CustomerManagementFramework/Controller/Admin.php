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
        $jsConfig = $this->getJsConfigHelper();
        $jsConfig->add('debug', \Pimcore::inDebugMode());

        foreach ($this->getJsConfigFeatures() as $feature) {
            $jsConfig->add($feature, true);
        }
    }

    /**
     * @return JsConfig
     */
    protected function getJsConfigHelper()
    {
        /** @var JsConfig $jsConfig */
        $jsConfig = $this->view->getHelper('JsConfig');

        return $jsConfig;
    }

    /**
     * Features to enable
     *
     * @return array
     */
    protected function getJsConfigFeatures()
    {
        return [
            '_init',
            'formAutoSubmit',
            'select2',
            'iCheck',
            'tooltip',
            'searchFilter',
            'collapsibleStateBox',
            'paginationFooterCount',
            'tableCollapse',
            'urlSelect',
            'modal',
            'pimcoreLink',
            'toggleGroup'
        ];
    }
}
