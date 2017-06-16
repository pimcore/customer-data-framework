<?php

namespace CustomerManagementFrameworkBundle\Model;

interface PersistentActivityInterface extends ActivityInterface {

    /**
     * save activity
     *
     * @return void
     */
    public function save();

    /**
     * delete activity
     *
     * @return void
     */
    public function delete();
}