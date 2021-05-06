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
     * @param string $operator
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
                $filter->applyOnCreateQuery($listing, $subQuery);
                $queryParts[] = $subQuery->getQueryPart('where');
            }
            $queryBuilder->andWhere(implode(' ' . $this->operator . ' ', $queryParts));
        }
    }
}
