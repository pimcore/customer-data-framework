<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 09.11.2016
 * Time: 14:14
 */

namespace CustomerManagementFramework\ActivityList;

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
            [ MariaDb::ACTIVITIES_TABLE ]
        );


        // add joins
      //  $this->addJoins($select);

        // add condition
     //   $this->addConditions($select);

        // group by
    //    $this->addGroupBy($select);

        // order
    //    $this->addOrder($select);

        // limit
      //  $this->addLimit($select);


        return $select;
    }

    public function load()
    {
        $query = $this->getQuery();

        $result = Db::get()->fetchAll($query);

        $this->totalCount = (int)Db::get()->fetchOne('SELECT FOUND_ROWS()');

        return $result;
    }
}