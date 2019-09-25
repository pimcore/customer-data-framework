<?php

namespace CustomerManagementFrameworkBundle\Model\ActivityList;

use CustomerManagementFrameworkBundle\Model\ActivityList\DefaultMariaDbActivityList\MySqlDao;

class MySqlActivityList extends DefaultMariaDbActivityList
{
    public function __construct()
    {
        parent::__construct();
        $this->dao = new MySqlDao($this);
    }
}