<?php

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;

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