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

namespace CustomerManagementFrameworkBundle\Twig\Extension;

use CustomerManagementFrameworkBundle\CustomerView\CustomerViewInterface;
use CustomerManagementFrameworkBundle\LinkGenerator\LinkActivityDefinitionLinkGenerator;
use CustomerManagementFrameworkBundle\Model\CustomerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Pimcore\Model\DataObject\LinkActivityDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CmfUrlUtilsExtension extends AbstractExtension
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var CustomerViewInterface
     */
    private $customerView;

    /**
     * @var LinkActivityDefinitionLinkGenerator
     */
    private $linkActivityUrlGenerator;

    /**
     * CmfUrlUtilsExtension constructor.
     *
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param CustomerViewInterface $customerView
     * @param LinkActivityDefinitionLinkGenerator $linkActivityUrlGenerator
     */
    public function __construct(RouterInterface $router, RequestStack $requestStack, CustomerViewInterface $customerView, LinkActivityDefinitionLinkGenerator $linkActivityUrlGenerator)
    {
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->customerView = $customerView;
        $this->linkActivityUrlGenerator = $linkActivityUrlGenerator;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions()//: array
    {
        return [
            new TwigFunction('cmf_filterFormAction', [$this, 'getFilterFormAction']),
            new TwigFunction('cmf_nextFormOrderParams', [$this, 'getNextFormOrderParams']),
            new TwigFunction('cmf_currentOrder', [$this, 'getCurrentOrder']),
            new TwigFunction('cmf_formQueryString', [$this, 'getFormQueryString']),
            new TwigFunction('cmf_userDetailUrl', [$this, 'getUserDetailUrl']),
            new TwigFunction('cmf_activityDefinitionUrl', [$this, 'getActivityDefinitionUrl']),
        ];
    }

    public function getFilterFormAction(PaginationInterface $paginator): string
    {
        // reset page when changing filters
        $formActionParams = [
            'page' => null,
            'perPage' => null,
        ];

        if (null !== $paginator && $paginator->getItemNumberPerPage() !== 25) {
            $formActionParams['perPage'] = $paginator->getItemNumberPerPage();
        }

        $request = $this->requestStack->getMainRequest();

        return $this->router->generate($request->get('_route'), $formActionParams);
    }

    public function getCurrentOrder($param): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request->get('order')) {
            return $request->get('order')[$param] ?? '';
        }

        return '';
    }

    public function getNextFormOrderParams($param): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $params = $request->query->all();

        $currentOrder = ($request->get('order') ? $request->get('order')[$param] ?? null : null);
        $nextOrder = '';
        if (empty($currentOrder)) {
            $nextOrder = 'ASC';
        } elseif (strtoupper($currentOrder) === 'ASC') {
            $nextOrder = 'DESC';
        }

        unset($params['order']); // only one order
        $params['order'][$param] = $nextOrder;

        return $params;
    }

    protected function getCurrentFormOrderParams(Request $request): array
    {
        $result = [];
        $order = $request->get('order');

        if (!is_array($order)) {
            return $result;
        }

        $validDirections = ['ASC', 'DESC'];
        foreach ($order as $field => $direction) {
            if (in_array($direction, $validDirections)) {
                $result[$field] = $direction;
            }
        }

        return $result;
    }

    protected function getFormFilterParams(Request $request): array
    {
        $result = [];
        $filters = $request->get('filter');

        if (!is_array($filters)) {
            return $result;
        }

        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function getQueryParams($includeOrder = true, $includeFilters = true): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $params = [];

        if ($includeOrder) {
            $params['order'] = $this->getCurrentFormOrderParams($request);
        }

        if ($includeFilters) {
            $params['filter'] = $this->getFormFilterParams($request);

            if ($fd = $request->get('filterDefinition')) {
                $params['filterDefinition'] = ['id' => $fd['id']];
            }
        }

        return $params;
    }

    public function getFormQueryString($url = null, $includeOrder = true, $includeFilters = true): string
    {
        $params = $this->getQueryParams($includeOrder, $includeFilters);
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
                    $url = $url.'?'.$queryString;
                } else {
                    $url = $url.'&'.$queryString;
                }
            }
        }

        return $url;
    }

    public function getUserDetailUrl(CustomerInterface $customer): ?string
    {
        $userDetailUrl = null;
        if ($this->customerView->hasDetailView($customer)) {
            $userDetailUrl = $this->router->generate('customermanagementframework_admin_customers_detail', [
                'id' => $customer->getId()
            ]);

            $userDetailUrl = $this->getFormQueryString($userDetailUrl);
        }

        return $userDetailUrl;
    }

    public function getActivityDefinitionUrl(LinkActivityDefinition $activityDefinition): ?string
    {
        return $this->linkActivityUrlGenerator->generate($activityDefinition);
    }
}
