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

namespace CustomerManagementFrameworkBundle\CustomReport\Adapter;

use CustomerManagementFrameworkBundle\Model\AbstractTermSegmentBuilderDefinition;
use Doctrine\DBAL\Exception;
use Pimcore\Bundle\CustomReportsBundle\Tool\Adapter\Sql;
use Pimcore\Db;
use Pimcore\Model;

class_exists(Sql::class);

class TermSegmentBuilder extends Sql
{
    /**
     * @param mixed $configuration
     *
     * @throws \Exception
     */
    public function getColumns($configuration): array
    {
        $columns = parent::getColumns($configuration);

        if ($columns[0] != 'term') {
            throw new \Exception(
                "SQL statement needs to return one single column named 'term' with all distinct terms."
            );
        }

        return $columns;
    }

    /**
     * @param mixed $filters
     * @param array $fields
     * @param bool $ignoreSelectAndGroupBy
     * @param array|null $drillDownFilters
     * @param string|null $selectField
     *
     * @throws Exception
     */
    protected function getBaseQuery(
        $filters,
        $fields,
        $ignoreSelectAndGroupBy = false,
        $drillDownFilters = null,
        $selectField = null
    ): ?array {
        $db = Db::get();
        $condition = ['1 = 1'];

        $sql = $this->buildQueryString($this->config, $ignoreSelectAndGroupBy, $drillDownFilters, $selectField);

        if (!$termDefinition = Model\DataObject\TermSegmentBuilderDefinition::getById($this->config->termDefinition)) {
            throw new \Exception('please select a term definition');
        }

        /**
         * @var AbstractTermSegmentBuilderDefinition $termDefinition
         */
        if (!$termDefinition instanceof AbstractTermSegmentBuilderDefinition) {
            throw new \Exception('term definition needs to be a subclass of AbstractTermSegmentBuilderDefinition');
        }

        $allMatchingTerms = $termDefinition->getAllPhrases();

        $allTerms = false;
        if (sizeof($allMatchingTerms)) {
            foreach ($allMatchingTerms as $term) {
                if (@preg_match($term, '') !== false) {
                    if ($allTerms === false) {
                        //MySQL regexp function doesn't work the same way like PHP regex matching => therfore we need to fetch all distinct terms and match them with PHP
                        $allTerms = $db->fetchFirstColumn($sql);
                    }
                    foreach ($allTerms as $t) {
                        if (@preg_match($term, $t)) {
                            $condition[] = 'term != '.$db->quote($t);
                        }
                    }
                } else {
                    $condition[] = 'term != '.$db->quote($term);
                }
            }
        }

        if ($filters) {
            if (is_array($filters)) {
                foreach ($filters as $filter) {
                    $value = $filter['value'];
                    $type = $filter['type'];
                    if ($type === 'date') {
                        $value = strtotime($value);
                    }
                    $operator = $filter['operator'];
                    switch ($operator) {
                        case 'like':
                            $condition[] = $db->quoteIdentifier($filter['property']).' LIKE '.$db->quote(
                                    '%'.$value.'%'
                                );
                            break;
                        case 'lt':
                        case 'gt':
                        case 'eq':

                            $compMapping = [
                                'lt' => '<',
                                'gt' => '>',
                                'eq' => '=',
                            ];

                            $condition[] = $db->quoteIdentifier(
                                    $filter['property']
                                ).' '.$compMapping[$operator].' '.$db->quote($value);
                            break;
                        case '=':
                            $condition[] = $db->quoteIdentifier($filter['property']).' = '.$db->quote($value);
                            break;
                    }
                }
            }
        }

        if (!preg_match('/(ALTER|CREATE|DROP|RENAME|TRUNCATE|UPDATE|DELETE) /i', $sql, $matches)) {
            $condition = implode(' AND ', $condition);

            $total = 'SELECT COUNT(*) FROM ('.$sql.') AS somerandxyz WHERE '.$condition;

            if ($fields) {
                $data = 'SELECT `'.implode('`, `', $fields).'` FROM ('.$sql.') AS somerandxyz WHERE '.$condition;
            } else {
                $data = 'SELECT * FROM ('.$sql.') AS somerandxyz WHERE '.$condition;
            }
        } else {
            return null;
        }

        return [
            'data' => $data,
            'count' => $total,
        ];
    }
}
