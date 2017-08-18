<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Controller;

use CustomerManagementFrameworkBundle\Templating\Helper\JsConfig;
use Pimcore\Bundle\AdminBundle\Controller\AdminController;
use Pimcore\Controller\EventedControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Zend\Paginator\Paginator;

class Admin extends AdminController implements EventedControllerInterface
{
    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->initJsConfig();
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
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
        $jsConfig = \Pimcore::getContainer()->get('pimcore.templating.view_helper.jsConfig');

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
            'toggleGroup',
        ];
    }

    /**
     * Build object paginator for filtered list
     *
     * @param Request $request
     * @param mixed $data
     * @param int $defaultPageSize
     *
     * @return Paginator
     */
    protected function buildPaginator(Request $request, $data, $defaultPageSize = null)
    {
        if (is_null($defaultPageSize)) {
            $defaultPageSize = \Pimcore::getContainer()->get(
                'pimcore.templating.view_helper.defaultPageSize'
            )->defaultPageSize();
        }

        $paginator = new Paginator($data);
        $paginator->setItemCountPerPage((int)$request->get('perPage', $defaultPageSize));
        $paginator->setCurrentPageNumber((int)$request->get('page', 1));

        return $paginator;
    }
}
