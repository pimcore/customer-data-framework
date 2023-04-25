<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Controller;

use CustomerManagementFrameworkBundle\Helper\JsConfigService;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Controller\KernelControllerEventInterface;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class Admin extends UserAwareController implements KernelControllerEventInterface
{
    use JsonHelperTrait;

    /**
     * @var JsConfigService
     */
    protected $jsConfigService;

    /**
     * @var int
     */
    protected $defaultPageSize = 25;

    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    public function __construct(JsConfigService $jsConfigService, PaginatorInterface $paginator)
    {
        $this->jsConfigService = $jsConfigService;
        $this->paginator = $paginator;
    }

    /**
     * @param ControllerEvent $event
     */
    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        $this->initJsConfig();
    }

    /**
     * Init JS config
     */
    protected function initJsConfig()
    {
        $jsConfig = $this->getJsConfigService();
        $jsConfig->add('debug', \Pimcore::inDebugMode());

        foreach ($this->getJsConfigFeatures() as $feature) {
            $jsConfig->add($feature, true);
        }
    }

    /**
     * @return JsConfigService
     */
    protected function getJsConfigService(): JsConfigService
    {
        return $this->jsConfigService;
    }

    /**
     * Features to enable
     *
     * @return array
     */
    protected function getJsConfigFeatures(): array
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
     */
    protected function buildPaginator(Request $request, mixed $data, int $defaultPageSize = null): PaginationInterface
    {
        if (is_null($defaultPageSize)) {
            $defaultPageSize = $this->defaultPageSize;
        }

        $page = (int)$request->get('page', 1);
        $pageSize = (int)$request->get('perPage', $defaultPageSize);

        return $this->paginator->paginate($data, $page, $pageSize);
    }
}
