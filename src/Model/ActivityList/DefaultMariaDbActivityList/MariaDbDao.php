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

namespace CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList;

use CustomerManagementFrameworkBundle\ActivityStore\MariaDb;
use CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList;
use Doctrine\DBAL\Query\QueryBuilder;
use Pimcore\Db;
use Pimcore\Model\AbstractModel;
use Pimcore\Model\Dao\DaoInterface;

class MariaDbDao implements DaoInterface
{
    /**
     * @var DefaultMariaDbActivityList
     */
    private $model;

    private $query;

    public function __construct(DefaultMariaDbActivityList $model)
    {
        $this->model = $model;
    }

    /**
     * get select query
     *
     * @param bool $clone
     *
     * @return QueryBuilder
     *
     * @throws \Exception
     */
    public function getQueryBuilder($clone = true)
    {
        if (is_null($this->query)) {
            // init
            $select = Db::get()->createQueryBuilder();

            // create base
            $select->from(MariaDb::ACTIVITIES_TABLE)
                ->select(
                    'id',
                    'customerId',
                    'activityDate',
                    'type',
                    'implementationClass',
                    'o_id',
                    'a_id',
                    'COLUMN_JSON(attributes) as attributes',
                    'md5',
                    'creationDate',
                    'modificationDate'
                );

            // add condition
            $this->addConditions($select);

            // order
            $this->addOrder($select);

            // limit
            $this->addLimit($select);

            $this->query = $select;
        }

        if ($clone) {
            return clone $this->query;
        }

        return $this->query;
    }

    public function setQuery(QueryBuilder $query = null)
    {
        $this->query = $query;
    }

    private function addLimit(QueryBuilder $select)
    {
        if ($limit = $this->model->getLimit()) {
            $select->setMaxResults($limit);
            $select->setFirstResult($this->model->getOffset());
        }
    }

    public function getCount()
    {
        $query = $this->getQueryBuilder();
        $query->setFirstResult(0);
        $query->setMaxResults(null);
        $query->resetQueryPart('from');
        $query->from(MariaDb::ACTIVITIES_TABLE);
        $query->select('count(*) totalCount');

        return Db::get()->fetchOne((string)$query, $this->model->getConditionVariables(), $this->model->getConditionVariableTypes());
    }

    public function load()
    {
        $query = $this->getQueryBuilder();

        $result = Db::get()->fetchAllAssociative((string)$query, $this->model->getConditionVariables(), $this->model->getConditionVariableTypes());

        return $result;
    }

    /**
     * @param QueryBuilder $select
     *
     * @return $this
     */
    protected function addConditions(QueryBuilder $select)
    {
        $condition = $this->model->getCondition();

        if ($condition) {
            $select->andWhere($condition);
        }

        return $this;
    }

    protected function addOrder(QueryBuilder $select)
    {
        $orderKey = $this->model->getOrderKey() ?: [];
        $order = $this->model->getOrder();

        foreach ($orderKey as $i => $key) {
            $orderString = str_replace('`', '', trim($key));
            $select->addOrderBy($orderString, $order[$i] ?? null);
        }

        return $this;
    }

    public function configure(): void
    {
    }

    public function setModel(AbstractModel $model): static
    {
        $this->model = $model;

        return $this;
    }
}
