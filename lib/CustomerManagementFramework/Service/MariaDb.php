<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 10.10.2016
 * Time: 11:22
 */

namespace CustomerManagementFramework\Service;

use Pimcore\Db;

class MariaDb {

    private function __construct()
    {

    }

    /**
     * @return static
     */
    private static $instance;
    public static function getInstance()
    {
        if(is_null(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }


    public function createDynamicColumnInsert(array $data) {

        $db = Db::get();

        $insert = '';
        $i=0;
        foreach($data as $key => $value) {
            $i++;
            if(!is_array($value)) {
                $insert .= "'" . $key . "'" . ','. $db->quote($value);
            } else {
                $insert .= "'" . $key . "'" . ','.$this->createDynamicColumnInsert($value);
            }

            if($i < sizeof($data)) {
                $insert .= ',';
            }
        }

        return "COLUMN_CREATE(" . $insert .  ")";
    }

    public function insert($tableName, array $data) {

        $db = Db::get();

        foreach($data as $key => $value) {
            if(is_null($value)) {
                unset($data[$key]);
            }
        }

        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)",
            $tableName,
            implode(',', array_keys($data)),
            implode(',', array_values($data)));

        $db->query($sql);
    }

    public function update($tableName, $data, $where) {
        $db = Db::get();

        $sql = "UPDATE " . $tableName . " SET ";

        $set = [];
        foreach($data as $key => $value) {
            if(is_null($value)) {
                $set[] = $key . ' = NULL';
            } else {
                $set[] = $key . ' = ' . $value;
            }

        }

        $sql .= implode(', ', $set);
        $sql .= " WHERE " . $where;

        $db->query($sql);
    }

}