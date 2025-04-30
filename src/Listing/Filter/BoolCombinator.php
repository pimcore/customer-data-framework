<?php

/**
 * This source file is available under the terms of the
 * Pimcore Open Core License (POCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (https://www.pimcore.com)
 *  @license    Pimcore Open Core License (POCL)
 */

namespace CustomerManagementFrameworkBundle\Listing\Filter;

use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Db;
use Pimcore\Model\DataObject\Listing as CoreListing;

class BoolCombinator extends AbstractFilter implements OnCreateQueryFilterInterface
{
    /**
     * @var OnCreateQueryFilterInterface[]
     */
    protected $filters;

    /**
     * @var string
     */
    protected $operator;

    /**
     * @param OnCreateQueryFilterInterface[] $filters
     */
    public function __construct(array $filters, string $operator = 'AND')
    {
        $this->filters = $filters;

        foreach ($this->filters as $filter) {
            if (!$filter instanceof OnCreateQueryFilterInterface) {
                throw new \Exception('Invalid filter, does not implement OnCreateQueryFilterInterface');
            }
        }

        if (!in_arrayi($operator, ['AND', 'OR'])) {
            throw new \InvalidArgumentException('Given operator is not valid');
        }

        $this->operator = $operator;
    }

    public function applyOnCreateQuery(CoreListing\Concrete $listing, QueryBuilder $queryBuilder)
    {
        if (count($this->filters) === 1) {
            $filter = $this->filters[0];
            $filter->applyOnCreateQuery($listing, $queryBuilder);
        } elseif (count($this->filters)) {
            $queryParts = [];
            foreach ($this->filters as $filter) {
                $subQuery = Db::get()->createQueryBuilder();
                $subQuery->select('1');
                $filter->applyOnCreateQuery($listing, $subQuery);
                $queryParts[] = str_replace('SELECT 1 WHERE', '', $subQuery->getSQL());
            }
            $queryBuilder->andWhere(implode(' ' . $this->operator . ' ', $queryParts));
        }
    }
}
