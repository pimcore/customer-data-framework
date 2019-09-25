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

use Pimcore\Db\ZendCompatibility\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\Helper\Helper;

class FormOrderParams extends Helper
{
    public function getName()
    {
        return 'formOrderParams';
    }

    /**
     * Get order params
     *
     * @param Request $request
     *
     * @return array
     */
    public function formOrderParams(Request $request)
    {
        $result = [];
        $order = $request->get('order');

        if (!is_array($order)) {
            return $result;
        }

        $validDirections = static::getValidDirections();
        foreach ($order as $field => $direction) {
            if (in_array($direction, $validDirections)) {
                $result[$field] = $direction;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public static function getValidDirections()
    {
        return [
            QueryBuilder::SQL_ASC,
            QueryBuilder::SQL_DESC,
        ];
    }

    /**
     * @param Request $request
     * @param $param
     * @return mixed
     */
    public function getNexSortOrder(Request $request, $param) {
        $val = $request->get('order')[$param];
        if ($val == null || empty($val)) {
            return QueryBuilder::SQL_ASC;
        } else if ($val == QueryBuilder::SQL_ASC) {
            return QueryBuilder::SQL_DESC;
        }
        return '';
    }


    /**
     * @param Request $request
     * @param $param
     * @return string
     */
    public function getOrderParams(Request $request, $param) {
        $params = $request->query->all();
        $order = $this->getNexSortOrder($request, $param);
        unset($params['order']); # only one order
        if (empty($order)) {
            unset($params['order'][$param]);
        } else {
            $params['order'][$param] = $order;
        }
        return $params;
    }
}
