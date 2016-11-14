<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 14:14
 */

namespace CustomerManagementFramework\ActivityList\DefaultMariaDbActivityList;

use CustomerManagementFramework\ActivityList\DefaultMariaDbActivityList;
use CustomerManagementFramework\ActivityStore\MariaDb;
use Pimcore\Db;

class Dao {

    /**
     * @var DefaultMariaDbActivityList
     */
    private $model;

    public function __construct(DefaultMariaDbActivityList $model) {
        $this->model = $model;
    }

    /**
     * get select query
     *
     * @return \Zend_Db_Select
     * @throws \Exception
     */
    public function getQuery()
    {
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
                'modificationDate'
            ]
        );


        // add joins
      //  $this->addJoins($select);

        // add condition
        $this->addConditions($select);

        // group by
    //    $this->addGroupBy($select);

        // order
    //    $this->addOrder($select);

        // limit
        $this->addLimit($select);


        return $select;
    }

    private function addLimit(\Zend_Db_Select $select) {
        if($limit = $this->model->getLimit()) {
            $select->limit($limit,  $this->model->getOffset());
        }
    }

    public function getCount() {
        $query = $this->getQuery();
        $query->limit(null,null);
        $query->reset("from");

        $query->from( MariaDb::ACTIVITIES_TABLE,
            [
                "totalCount" => "count(*)"
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
     * @param \Zend_DB_Select $select
     *
     * @return $this
     */
    protected function addConditions(\Zend_DB_Select $select)
    {
        $condition = $this->model->getCondition();

        if ($condition) {
            $select->where($condition, $this->model->getConditionVariables());
        }

        return $this;
    }
}