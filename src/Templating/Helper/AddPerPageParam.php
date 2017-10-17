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

class AddPerPageParam extends Helper
{
    public function getName()
    {
        return 'addPerPageParam';
    }

    /**
     * Add perPage param if set and not the default value
     *
     * @param array $params
     * @param int $defaultPageSize
     *
     * @return array
     */
    public function add(array $params = [], $defaultPageSize = null)
    {

        /**
         * @var Request $request
         */
        $request = \Pimcore::getContainer()->get('request_stack')->getMasterRequest();

        if (null === $defaultPageSize) {
            $defaultPageSize = 25;
        } else {
            $defaultPageSize = (int)$defaultPageSize;
        }

        $perPageParam = (int)$request->get('perPage', 0);
        if ($perPageParam <= 0) {
            return $params;
        }

        if ($perPageParam !== $defaultPageSize) {
            $params['perPage'] = $perPageParam;
        }

        return $params;
    }
}
