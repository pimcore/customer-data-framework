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

class FormFilterParams extends Helper
{
    public function getName()
    {
        return 'formFilterParams';
    }

    /**
     * Returns the helper instance. If a request is given, call formQueryString() directly.
     *
     * @param string|null $varName
     *
     * @return string|self
     */
    public function __invoke(Request $request = null)
    {
        if (is_null($request)) {
            return $this;
        }

        return $this->formFilterParams($request);
    }

    /**
     * Get filter params with values
     *
     * @param Request $request
     *
     * @return array
     */
    public function formFilterParams(Request $request)
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
}
