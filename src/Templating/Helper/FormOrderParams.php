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
}
