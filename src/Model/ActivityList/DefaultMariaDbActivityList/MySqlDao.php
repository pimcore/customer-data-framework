<?php

namespace CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList;

use CustomerManagementFrameworkBundle\ActivityStore\MariaDb;
use CustomerManagementFrameworkBundle\Model\ActivityList\MySqlActivityList;
use Pimcore\Db;

class MySqlDao
{
    /**
     * @var MySqlActivityList
     */
    private $model;

    private $query;

    public function __construct(MySqlActivityList $model)
    {
        $this->model = $model;
    }

    /**
     * get select query
     *
     * @param bool $clone
     *
     * @return Db\ZendCompatibility\QueryBuilder
     *
     * @throws \Exception
     */
    public function getQuery($clone = true)
    {
        if (is_null($this->query)) {
            // init
            $select = Db::get()->select();

            // create base
            $select->from(
                MariaDb::ACTIVITIES_TABLE,
                [
                    'id',
                    'customerId',
                    'activityDate',
                    'type',
                    'implementationClass',
                    'o_id',
                    'a_id',
                    'attributes' => 'COLUMN_JSON(attributes)',
                    'md5',
                    'creationDate',
                    'modificationDate',
                ]
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

    public function setQuery(Db\ZendCompatibility\QueryBuilder $query = null)
    {
        $this->query = $query;
    }

    private function addLimit(Db\ZendCompatibility\QueryBuilder $select)
    {
        if ($limit = $this->model->getLimit()) {
            $select->limit($limit, $this->model->getOffset());
        }
    }

    public function getCount()
    {
        $query = $this->getQuery();
        $query->limit(null, null);
        $query->reset('from');

        $query->from(
            MariaDb::ACTIVITIES_TABLE,
            [
                'totalCount' => 'count(*)',
            ]
        );

        return Db::get()->fetchOne($query);
    }

    public function load()
    {
        $query = $this->getQuery();

        $result = Db::get()->fetchAll($query);

        return $result;
    }

    /**
     * @param Db\ZendCompatibility\QueryBuilder $select
     *
     * @return $this
     */
    protected function addConditions(Db\ZendCompatibility\QueryBuilder $select)
    {
        $condition = $this->model->getCondition();

        if ($condition) {
            if ($conditionVariables = $this->model->getConditionVariables()) {
                $select->where($condition, $conditionVariables);
            } else {
                $select->where($condition);
            }
        }

        return $this;
    }

    protected function addOrder(Db\ZendCompatibility\QueryBuilder $select)
    {
        $orderKey = $this->model->getOrderKey() ?: [];
        $order = $this->model->getOrder();

        foreach ($orderKey as $i => $key) {
            $orderString = str_replace('`', '', trim($key));
            if ($order[$i]) {
                $orderString .= ' '.$order[$i];
            }

            $select->order($orderString);
        }

        return $this;
    }
}