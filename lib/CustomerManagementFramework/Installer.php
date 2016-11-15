<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 14:06
 */

namespace CustomerManagementFramework;

class Installer {

    public function install() {

        $this->installPermissions();
        return true;
    }

    private function installPermissions() {

        $permissions = ["plugin_customermanagementframework_activityview"];

        foreach($permissions as $key) {
            $permission = new \Pimcore\Model\User\Permission\Definition();
            $permission->setKey($key);

            $res = new \Pimcore\Model\User\Permission\Definition\Dao();
            $res->configure(\Pimcore\Db::get());
            $res->setModel($permission);
            $res->save();
        }
    }
}