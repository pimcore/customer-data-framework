<?php

namespace CustomerManagementFramework\Model;

use Carbon\Carbon;
use CustomerManagementFramework\ActivityStoreEntry\ActivityStoreEntryInterface;

interface PersistentActivityInterface extends ActivityInterface {

   public function save();

   public function delete();
}