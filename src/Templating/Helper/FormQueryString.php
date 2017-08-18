<?php

/**
 * Pimcore Customer Management Framework Bundle
 * Full copyright and license information is available in
 * License.md which is distributed with this source code.
 *
 * @copyright  Copyright (C) Elements.at New Media Solutions GmbH
 * @license    GPLv3
 */

namespace CustomerManagementFrameworkBundle\Templating\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;

class FormQueryString extends Helper
{
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
        /** @var FormFilterParams $helper */
        $helper = \Pimcore::getContainer()->get('pimcore.templating.view_helper.formFilterParams');
        $params = $helper->formFilterParams($request);

        return $params;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getOrderParams(Request $request)
    {
        /** @var FormOrderParams $helper */
        $helper = \Pimcore::getContainer()->get('pimcore.templating.view_helper.formOrderParams');
        $params = $helper->formOrderParams($request);

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
