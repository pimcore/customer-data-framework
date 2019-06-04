<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;

class FormQueryString extends Helper
{
    /**
     * @var FormFilterParams
     */
    protected $formFilterParams;

    /**
     * @var FormOrderParams
     */
    protected $formOrderParams;

    public function __construct(FormFilterParams $formFilterParams, FormOrderParams $formOrderParams)
    {
        $this->formFilterParams = $formFilterParams;
        $this->formOrderParams = $formOrderParams;

    }

    public function getName()
    {
        return 'formQueryString';
    }

    /**
     * Returns the helper instance. If a request is given, call formQueryString() directly.
     *
     * @param string|null $varName
     *
     * @return string|self
     */
    public function __invoke(Request $request = null, $url = null, $includeOrder = true, $includeFilters = true)
    {
        if (is_null($request)) {
            return $this;
        }

        return $this->formQueryString($request, $url, $includeOrder, $includeFilters);
    }

    /**
     * Add filter query string to URL
     *
     * @param Request $request
     * @param $url
     * @param bool $includeOrder
     * @param bool $includeFilters
     *
     * @return string
     */
    public function formQueryString(Request $request, $url, $includeOrder = true, $includeFilters = true)
    {
        return $this->addQueryStringToUrl($url, $this->getQueryParams($request, $includeOrder, $includeFilters));
    }

    /**
     * Get filter query params from request
     *
     * @param Request $request
     * @param bool $includeOrder
     * @param bool $includeFilters
     *
     * @return array
     */
    public function getQueryParams(Request $request, $includeOrder = true, $includeFilters = true)
    {
        $params = [];

        if ($includeOrder) {
            $params['order'] = $this->getOrderParams($request);
        }

        if ($includeFilters) {
            $params['filter'] = $this->getFilterParams($request);

            if($fd = $request->get('filterDefinition')) {
                $params['filterDefinition'] = ['id'=>$fd['id']];
            }
        }

        return $params;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getFilterParams(Request $request)
    {
        $params = $this->formFilterParams->formFilterParams($request);
        return $params;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getOrderParams(Request $request)
    {
        $params = $this->formOrderParams->formOrderParams($request);
        return $params;
    }

    /**
     * Add filter query string to URL
     *
     * @param $url
     * @param array $params
     *
     * @return string
     */
    public function addQueryStringToUrl($url, array $params = [])
    {
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
}
